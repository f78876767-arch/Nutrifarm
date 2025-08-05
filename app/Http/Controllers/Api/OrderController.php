<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Cart;
use App\Models\CartProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\XenditService;

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
        $request->validate([
            'shipping_method' => 'required|string',
            'payment_method' => 'required|string',
        ]);
        $cart = Cart::where('user_id', Auth::id())->firstOrFail();
        $cartProducts = $cart->cartProducts;
        if ($cartProducts->isEmpty()) {
            return response()->json(['error' => 'Cart is empty'], 400);
        }
        DB::beginTransaction();
        try {
            $total = $cartProducts->sum(function ($item) {
                return $item->quantity * $item->product->price;
            });
            $order = Order::create([
                'user_id' => Auth::id(),
                'total' => $total,
                'status' => 'pending',
                'shipping_method' => $request->shipping_method,
                'payment_method' => $request->payment_method,
            ]);
            foreach ($cartProducts as $item) {
                OrderProduct::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->product->price,
                ]);
            }
            $cart->cartProducts()->delete();

            // Xendit integration: create invoice
            $invoice = $xenditService->createInvoice([
                'external_id' => 'order-' . $order->id,
                'payer_email' => Auth::user()->email,
                'description' => 'Order #' . $order->id,
                'amount' => $order->total,
            ]);
            // Optionally, save invoice_url and id to order (add columns if needed)
            $order->xendit_invoice_id = $invoice['id'] ?? null;
            $order->xendit_invoice_url = $invoice['invoice_url'] ?? null;
            $order->save();

            DB::commit();
            return response()->json([
                'order' => $order->load('orderProducts.product'),
                'xendit_invoice' => $invoice,
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
}
