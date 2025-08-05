<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartProduct;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function index()
    {
        $cart = Cart::with('cartProducts.product')->where('user_id', Auth::id())->first();
        return $cart;
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);
        $cart = Cart::firstOrCreate(['user_id' => Auth::id()]);
        $cartProduct = CartProduct::updateOrCreate(
            [
                'cart_id' => $cart->id,
                'product_id' => $request->product_id,
            ],
            [
                'quantity' => $request->quantity,
            ]
        );
        return response()->json($cartProduct, 201);
    }

    public function update(Request $request, $id)
    {
        $cartProduct = CartProduct::findOrFail($id);
        $cartProduct->update($request->only('quantity'));
        return response()->json($cartProduct);
    }

    public function destroy($id)
    {
        $cartProduct = CartProduct::findOrFail($id);
        $cartProduct->delete();
        return response()->json(null, 204);
    }
}
