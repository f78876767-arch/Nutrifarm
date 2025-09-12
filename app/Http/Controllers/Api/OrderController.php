<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\XenditService;
use App\Helpers\CurrencyHelper;
use App\Models\Product;
use App\Models\Variant;
use App\Models\Invoice;
use App\Services\DocumentService;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function index()
    {
        return Order::with('orderProducts.product')->where('user_id', Auth::id())->get();
    }

    public function show($id)
    {
        $order = Order::with('orderProducts.product')->where('user_id', Auth::id())->findOrFail($id);
        return $order;
    }

    public function store(Request $request, XenditService $xenditService)
    {
        $request->validate(['shipping_method' => 'required|string','payment_method' => 'required|string']);

        $user = Auth::user();
        $items = [];
        $subtotal = 0;
        $totalDiscount = 0; // reserved for future promotions

        if ($request->has('items') && is_array($request->items) && count($request->items) > 0) {
            foreach ($request->items as $input) {
                $product = Product::findOrFail($input['product_id']);
                $variant = isset($input['variant_id']) ? Variant::find($input['variant_id']) : null;
                if ($variant && $variant->product_id !== $product->id) {
                    $variant = null; // safety: ignore mismatched variant
                }
                $qty = (int)($input['quantity'] ?? 1);

                // NEW: trust frontend price when provided, fallback to server effective price
                $expectedPrice = (float) ($variant ? $variant->effective_price : $product->effective_price);
                $clientPrice = array_key_exists('price', $input) ? (float) $input['price'] : null;
                $unitPrice = $clientPrice !== null ? $clientPrice : $expectedPrice;

                // Optional audit: log mismatch over 1 IDR but do not block
                if ($clientPrice !== null && abs($clientPrice - $expectedPrice) > 1) {
                    Log::warning('order.price_mismatch', [
                        'product_id' => $product->id,
                        'variant_id' => $variant?->id,
                        'client_price' => $clientPrice,
                        'expected_price' => $expectedPrice,
                    ]);
                }

                $lineSubtotal = $unitPrice * $qty;
                $subtotal += $lineSubtotal;
                $items[] = [
                    'product' => $product,
                    'variant' => $variant,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'line_subtotal' => $lineSubtotal,
                ];
            }
        } else {
            $cartItems = CartItem::where('user_id', $user->id)
                ->with(['product', 'variant'])
                ->get();

            if ($cartItems->isEmpty()) {
                return response()->json(['error' => 'Cart is empty'], 400);
            }

            foreach ($cartItems as $ci) {
                $product = $ci->product;
                if (!$product) { continue; }
                $variant = $ci->variant;
                $qty = (int) $ci->quantity;
                $unitPrice = (float) ($variant ? $variant->effective_price : $product->effective_price);
                $lineSubtotal = $unitPrice * $qty;
                $subtotal += $lineSubtotal;
                $items[] = [
                    'product' => $product,
                    'variant' => $variant,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'line_subtotal' => $lineSubtotal,
                ];
            }
        }

        $total = $subtotal - $totalDiscount;

        DB::beginTransaction();
        try {
            $externalId = 'nutrifarm-order-' . uniqid();
            $order = Order::create([
                'user_id' => $user->id,
                'total' => $total,
                'status' => 'pending',
                'shipping_method' => $request->shipping_method,
                'payment_method' => $request->payment_method,
                'payment_status' => 'pending',
                'external_id' => $externalId,
            ]);

            foreach ($items as $row) {
                OrderProduct::create([
                    'order_id' => $order->id,
                    'product_id' => $row['product']->id,
                    'variant_id' => $row['variant'] ? $row['variant']->id : null,
                    'quantity' => $row['quantity'],
                    'price' => $row['unit_price'], // save client-trusted price
                ]);
            }

            // Build success/failure redirect URLs for Xendit invoice
            $successUrl = route('xendit.redirect.success', ['external_id' => $externalId]);
            $failureUrl = route('xendit.redirect.failure', ['external_id' => $externalId]);

            $invoice = $xenditService->createInvoice([
                'external_id' => $externalId,
                'payer_email' => $user->email,
                'description' => 'Order #' . $order->id,
                'amount' => $order->total, // now matches client cart sum
                'success_redirect_url' => $successUrl,
                'failure_redirect_url' => $failureUrl,
                // Expire the invoice if not paid within 1 hour
                'invoice_duration' => 3600,
            ]);

            // Update order with Xendit details
            $order->update([
                'xendit_invoice_id' => $invoice['id'] ?? null,
                'xendit_invoice_url' => $invoice['invoice_url'] ?? null,
            ]);

            // Persist invoice record
            Invoice::create([
                'xendit_id' => $invoice['id'] ?? null,
                'external_id' => $invoice['external_id'] ?? $externalId,
                'user_id' => $user->id,
                'order_id' => $order->id,
                'status' => $invoice['status'] ?? 'PENDING',
                'amount' => $invoice['amount'] ?? $order->total,
                'invoice_url' => $invoice['invoice_url'] ?? null,
                'payer_email' => $invoice['payer_email'] ?? $user->email,
                'description' => $invoice['description'] ?? ('Order #' . $order->id),
                'currency' => $invoice['currency'] ?? 'IDR',
                'expiry_date' => $invoice['expiry_date'] ?? null,
                'raw' => $invoice,
            ]);

            // Generate internal invoice PDF for the customer to view
            $doc = app(DocumentService::class)->generateInvoicePdf($order);
            $order->update(['invoice_pdf_url' => $doc['url']]);

            // Clear DB cart only when items were sourced from DB cart
            if (!($request->has('items') && is_array($request->items) && count($request->items) > 0)) {
                CartItem::where('user_id', $user->id)->delete();
            }

            DB::commit();

            return response()->json([
                'order' => $order->load('orderProducts.product'),
                'invoice' => $invoice,
                'redirect_url' => $invoice['invoice_url'] ?? null,
                'invoice_pdf_url' => $order->invoice_pdf_url,
                'receipt_pdf_url' => $order->receipt_pdf_url,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $order = Order::where('user_id', Auth::id())->findOrFail($id);
        $order->update($request->only(['status', 'resi', 'payment_status']));
        return response()->json($order);
    }

    public function destroy($id)
    {
        $order = Order::where('user_id', Auth::id())->findOrFail($id);
        $order->delete();
        return response()->json(null, 204);
    }

    // New: expose invoice/receipt URLs
    public function invoiceDoc($id)
    {
        $order = Order::where('user_id', Auth::id())->findOrFail($id);
        return response()->json(['invoice_pdf_url' => $order->invoice_pdf_url]);
    }

    public function receiptDoc($id)
    {
        $order = Order::where('user_id', Auth::id())->findOrFail($id);
        $order->load('orderProducts.product');
        return response()->json(['receipt_pdf_url' => $order->receipt_pdf_url, 'paid_at' => $order->paid_at]);
    }

    public function reviewable($orderId)
    {
        $user = auth()->user();
        $order = Order::with(['orderProducts.product','orderProducts.variant','orderProducts.review'])->findOrFail($orderId);
        if ($order->user_id !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        if ($order->status !== 'completed') {
            return response()->json(['reviewable' => false, 'items' => []]);
        }

        $items = $order->orderProducts->map(function ($op) {
            return [
                'order_product_id' => $op->id,
                'product' => [
                    'id' => $op->product_id,
                    'name' => $op->product->name ?? '',
                    'image' => $op->product->image_url ?? null,
                ],
                'variant' => $op->variant ? [
                    'id' => $op->variant_id,
                    'name' => $op->variant->name,
                    'value' => $op->variant->value,
                ] : null,
                'quantity' => $op->quantity,
                'already_reviewed' => (bool) $op->review,
                'review' => $op->review ? [
                    'rating' => $op->review->rating,
                    'comment' => $op->review->comment,
                ] : null,
            ];
        });

        return response()->json(['reviewable' => true, 'items' => $items]);
    }
}
