<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartProduct;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $query = Cart::with(['user', 'cartProducts.product']);
        
        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
            });
        }
        
        // Filter by user
        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }
        
        // Filter by cart status
        if ($request->has('status')) {
            if ($request->status === 'active') {
                $query->whereHas('cartProducts');
            } elseif ($request->status === 'abandoned') {
                $query->where('updated_at', '<', now()->subDays(7))
                     ->whereHas('cartProducts');
            }
        }
        
        $carts = $query->latest('updated_at')->paginate(15);
        $users = User::orderBy('name')->get();
        
        // Get statistics
        $stats = [
            'total_carts' => Cart::whereHas('cartProducts')->count(),
            'abandoned_carts' => Cart::where('updated_at', '<', now()->subDays(7))
                ->whereHas('cartProducts')->count(),
            'total_cart_value' => CartProduct::join('products', 'cart_products.product_id', '=', 'products.id')
                ->sum(DB::raw('cart_products.quantity * products.price')),
            'avg_cart_items' => CartProduct::avg('quantity'),
        ];
        
        return view('admin.carts.index', compact('carts', 'users', 'stats'));
    }

    public function show(Cart $cart)
    {
        $cart->load(['user', 'cartProducts.product']);
        
        // Calculate cart totals
        $totalItems = $cart->cartProducts->sum('quantity');
        $totalValue = $cart->cartProducts->sum(function($item) {
            return $item->quantity * $item->product->price;
        });
        
        return view('admin.carts.show', compact('cart', 'totalItems', 'totalValue'));
    }

    public function destroy(Cart $cart)
    {
        $cart->cartProducts()->delete();
        $cart->delete();
        return redirect()->route('admin.carts.index')->with('success', 'Cart deleted successfully');
    }

    /**
     * Remove specific item from cart
     */
    public function removeItem(CartProduct $cartProduct)
    {
        $cart = $cartProduct->cart;
        $cartProduct->delete();
        
        return redirect()->route('admin.carts.show', $cart)
            ->with('success', 'Item removed from cart successfully');
    }

    /**
     * Get abandoned carts for marketing campaigns
     */
    public function abandoned()
    {
        $abandonedCarts = Cart::with(['user', 'cartProducts.product'])
            ->where('updated_at', '<', now()->subDays(1))
            ->whereHas('cartProducts')
            ->latest('updated_at')
            ->paginate(15);

        return view('admin.carts.abandoned', compact('abandonedCarts'));
    }

    /**
     * Send abandonment recovery email
     */
    public function sendRecoveryEmail(Cart $cart)
    {
        // Here you would implement email sending logic
        // For now, just return success message
        
        return redirect()->back()->with('success', 'Recovery email sent to ' . $cart->user->email);
    }
}
