<?php
namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\XenditService;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;

class XenditPaymentController extends Controller
{
    protected $xendit;

    public function __construct(XenditService $xendit)
    {
        $this->xendit = $xendit;
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

        $user = $request->user();
        $orderId = $request->input('order_id');

        DB::beginTransaction();
        try {
            $invoice = $this->xendit->createInvoice($params);
            // Store invoice in DB
            Invoice::create([
                'xendit_id' => $invoice['id'],
                'external_id' => $invoice['external_id'],
                'user_id' => $user ? $user->id : null,
                'order_id' => $orderId,
                'status' => $invoice['status'],
                'amount' => $invoice['amount'],
                'invoice_url' => $invoice['invoice_url'],
                'payer_email' => $invoice['payer_email'] ?? null,
                'description' => $invoice['description'] ?? null,
                'currency' => $invoice['currency'] ?? null,
                'expiry_date' => $invoice['expiry_date'] ?? null,
                'raw' => $invoice,
            ]);
            DB::commit();
            return response()->json($invoice);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function handleCallback(Request $request)
    {
        $data = $request->all();
        Log::info('Xendit callback received FULL DATA', ['data' => $data]);
        Log::info('Xendit callback id field', ['id' => $data['id'] ?? null]);
        // Find invoice by xendit_id
        $invoice = Invoice::where('xendit_id', $data['id'] ?? null)->first();
        if ($invoice) {
            Log::info('Invoice found for callback', ['xendit_id' => $invoice->xendit_id, 'status_before' => $invoice->status]);
            $invoice->status = $data['status'] ?? $invoice->status;
            $invoice->raw = $data;
            $invoice->save();
            Log::info('Invoice updated', ['xendit_id' => $invoice->xendit_id, 'status_after' => $invoice->status]);
            // Update related order payment_status if invoice is paid
            if ($invoice->order && ($data['status'] ?? null) === 'PAID') {
                $invoice->order->payment_status = 'paid';
                $invoice->order->save();
                Log::info('Order payment_status updated to paid', ['order_id' => $invoice->order->id]);
            }
        } else {
            Log::warning('No invoice found for callback', ['xendit_id' => $data['id'] ?? null]);
        }
        return response()->json(['status' => 'ok']);
    }
}
