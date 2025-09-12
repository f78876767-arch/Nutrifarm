<?php
namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\XenditService;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderHistory;
use App\Models\CartItem;
use App\Models\OrderProduct;
use App\Models\Variant;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Services\DocumentService;
use App\Notifications\OrderPaidNotification;

class XenditPaymentController extends Controller
{
    protected $xendit;

    public function __construct(XenditService $xendit)
    {
        $this->xendit = $xendit;
        // Lightweight throttling for webhook endpoint (60 per minute per IP)
        $this->middleware('throttle:60,1')->only('handleCallback');
    }

    public function createInvoice(Request $request)
    {
        $request->validate([
            'external_id' => 'required|string',
            'payer_email' => 'required|email',
            'description' => 'required|string',
            'amount' => 'required|numeric|min:1000',
        ]);
        $params = $request->only(['external_id', 'payer_email', 'description', 'amount']);
        $params['success_redirect_url'] = $request->input('success_redirect_url');
        $params['failure_redirect_url'] = $request->input('failure_redirect_url');
        $params['currency'] = 'IDR';
        // Expire invoice automatically if unpaid after 1 hour (allow override)
        $params['invoice_duration'] = (int) $request->input('invoice_duration', 3600);

        $user = $request->user();
        $orderId = $request->input('order_id');

        DB::beginTransaction();
        try {
            $invoice = $this->xendit->createInvoice($params);
            // Store invoice in DB
            Invoice::create([
                'xendit_id' => $invoice['id'] ?? null,
                'external_id' => $invoice['external_id'] ?? ($params['external_id'] ?? null),
                'user_id' => $user ? $user->id : null,
                'order_id' => $orderId,
                'status' => $invoice['status'] ?? null,
                'amount' => $invoice['amount'] ?? null,
                'invoice_url' => $invoice['invoice_url'] ?? null,
                'payer_email' => $invoice['payer_email'] ?? null,
                'description' => $invoice['description'] ?? null,
                'currency' => $invoice['currency'] ?? 'IDR',
                'expiry_date' => $invoice['expiry_date'] ?? null,
                'raw' => $invoice,
            ]);

            // Optionally sync onto Order if provided
            if ($orderId) {
                $order = Order::find($orderId);
                if ($order) {
                    $order->update([
                        'xendit_invoice_id' => $invoice['id'] ?? null,
                        'xendit_invoice_url' => $invoice['invoice_url'] ?? null,
                    ]);
                }
            }

            DB::commit();
            return response()->json($invoice);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function handleCallback(Request $request)
    {
        // Optional: verify callback token
        $expectedToken = env('XENDIT_CALLBACK_TOKEN');
        $providedToken = $request->header('X-Callback-Token');
        if ($expectedToken && $providedToken !== $expectedToken) {
            Log::warning('xendit.callback.unauthorized', []);
            // still return 200 to avoid retries or switch to 401 as policy
            return response()->json(['success' => true]);
        }

        $raw = $request->all();
        $data = isset($raw['data']) && is_array($raw['data']) ? $raw['data'] : $raw; // support nested payloads
        $status = strtoupper((string) ($data['status'] ?? ''));
        $externalId = $data['external_id'] ?? null; // matches order external_id for Invoice events
        $xenditId = $data['id'] ?? null;

        Log::info('xendit.callback.received', ['external_id' => $externalId, 'status' => $status, 'id' => $xenditId]);

        // Try fetch invoice detail to enrich data if we have id
        $invoiceDetails = null;
        if ($xenditId) {
            try {
                $invoiceDetails = $this->xendit->getInvoice($xenditId);
                $externalId = $externalId ?: ($invoiceDetails['external_id'] ?? null);
                $status = $status ?: strtoupper((string) ($invoiceDetails['status'] ?? ''));
            } catch (\Throwable $e) {
                Log::warning('xendit.invoice.fetch_failed', ['id' => $xenditId, 'error' => $e->getMessage()]);
            }
        }

        $order = $externalId ? Order::where('external_id', $externalId)->first() : null;
        if (!$order) {
            Log::warning('xendit.callback.order_not_found', ['external_id' => $externalId]);
            return response()->json(['success' => true]);
        }

        // Idempotency
        if ($order->payment_status === 'paid' && $status === 'PAID') {
            return response()->json(['success' => true]);
        }

        try {
            DB::transaction(function () use ($order, $raw, $data, $status, $xenditId, $invoiceDetails) {
                $mapped = match ($status) {
                    'PAID', 'SUCCEEDED' => 'paid',
                    'EXPIRED' => 'expired',
                    'FAILED', 'SETTLED_CANCELED', 'CANCELED' => 'failed',
                    default => $order->status,
                };

                $updates = [
                    'status' => $mapped,
                    'payment_status' => $mapped,
                    'paid_at' => $mapped === 'paid' ? now() : null,
                ];
                if ($xenditId) {
                    $updates['xendit_invoice_id'] = $xenditId;
                }
                if (($invoiceDetails['invoice_url'] ?? null)) {
                    $updates['xendit_invoice_url'] = $invoiceDetails['invoice_url'];
                }
                $order->update($updates);

                // Upsert invoice minimal fields
                if ($xenditId) {
                    $inv = Invoice::firstOrNew(['xendit_id' => $xenditId]);
                    $inv->external_id = $inv->external_id ?: $order->external_id;
                    $inv->user_id = $inv->user_id ?: $order->user_id;
                    $inv->order_id = $inv->order_id ?: $order->id;
                    $inv->status = $invoiceDetails['status'] ?? ($data['status'] ?? $inv->status);
                    $inv->amount = $invoiceDetails['amount'] ?? ($data['amount'] ?? $inv->amount);
                    $inv->invoice_url = $invoiceDetails['invoice_url'] ?? ($data['invoice_url'] ?? $inv->invoice_url);
                    $inv->payer_email = $invoiceDetails['payer_email'] ?? ($data['payer_email'] ?? $inv->payer_email);
                    $inv->description = $invoiceDetails['description'] ?? ($data['description'] ?? $inv->description);
                    $inv->currency = $invoiceDetails['currency'] ?? ($data['currency'] ?? $inv->currency ?? 'IDR');
                    $inv->expiry_date = $invoiceDetails['expiry_date'] ?? ($data['expiry_date'] ?? $inv->expiry_date);
                    $inv->raw = $raw;
                    $inv->save();
                }

                // Record order history idempotently: avoid duplicates of same status from callback
                $existingSame = $order->relationLoaded('histories') ? $order->histories()->where('status', $mapped)->exists() : $order->histories()->where('status', $mapped)->exists();
                if (!$existingSame) {
                    OrderHistory::create([
                        'order_id' => $order->id,
                        'status' => $mapped,
                        'note' => 'Xendit callback',
                        'metadata' => $raw,
                    ]);
                }

                // Clear cart and decrement stock on paid only
                if ($mapped === 'paid') {
                    // Decrement stock per order item (variant-only)
                    $items = $order->orderProducts()->get();
                    foreach ($items as $it) {
                        if (!empty($it->variant_id)) {
                            Variant::where('id', $it->variant_id)->decrement('stock_quantity', max(1, (int) $it->quantity));
                        }
                    }

                    CartItem::where('user_id', $order->user_id)->delete();

                    // Generate receipt PDF
                    $doc = app(DocumentService::class)->generateReceiptPdf($order->fresh('orderProducts.product'));
                    $order->update(['receipt_pdf_url' => $doc['url']]);

                    // Notify user: database + push
                    if ($order->user) {
                        $notif = new OrderPaidNotification($order->id, $order->invoice_no ?? (string) $order->id, (float) $order->total);
                        $order->user->notify($notif);
                        $notif->sendPush($order->user);
                    }
                }
            });
        } catch (\Throwable $e) {
            Log::error('xendit.callback.error', ['error' => $e->getMessage()]);
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => true]);
    }
}
