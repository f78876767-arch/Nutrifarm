<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index()
    {
        $carts = CartItem::with(['user','product'])->orderByDesc('created_at')->paginate(20);
        return view('admin.carts.index', compact('carts'));
    }

    public function show($cart)
    {
        $items = CartItem::with(['user','product'])->where('user_id', $cart)->get();
        return view('admin.carts.show', compact('items', 'cart'));
    }

    public function destroy($cart)
    {
        CartItem::where('user_id', $cart)->delete();
        return redirect()->route('admin.carts.index')->with('status','Cart cleared');
    }

    public function abandoned()
    {
        $items = CartItem::with(['user','product'])->where('updated_at', '<', now()->subDays(3))->paginate(20);
        return view('admin.carts.abandoned', compact('items'));
    }

    public function removeItem($cartProduct)
    {
        CartItem::where('id', $cartProduct)->delete();
        return back()->with('status','Item removed');
    }

    public function sendRecoveryEmail($cart)
    {
        return back()->with('status','Recovery email queued');
    }
}
