<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Get user's cart items
     */
    public function index(Request $request)
    {
        $cartItems = $request->user()->cartItems()
            ->with(['product.category', 'variant'])
            ->get();

        // Calculate totals
        $subtotal = $cartItems->sum(fn($item) => $item->getTotalPrice());
        $totalItems = $cartItems->sum('quantity');

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $cartItems->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'product_id' => $item->product_id,
                        'variant_id' => $item->variant_id,
                        'quantity' => $item->quantity,
                        'price' => $item->getPrice(),
                        'total_price' => $item->getTotalPrice(),
                        'product' => [
                            'id' => $item->product->id,
                            'name' => $item->product->name,
                            'image_url' => $item->product->image_url,
                            'category' => $item->product->category->name ?? null,
                        ],
                        'variant' => $item->variant ? [
                            'id' => $item->variant->id,
                            'type' => $item->variant->type,
                            'value' => $item->variant->value,
                            'unit' => $item->variant->unit,
                            'price' => $item->variant->price,
                        ] : null,
                    ];
                }),
                'summary' => [
                    'subtotal' => $subtotal,
                    'total_items' => $totalItems,
                    'shipping' => 0, // Add shipping logic later
                    'total' => $subtotal, // subtotal + shipping
                ]
            ]
        ]);
    }

    /**
     * Add item to cart
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'variant_id' => 'nullable|exists:variants,id',
            'quantity' => 'required|integer|min:1',
        ]);

        // Check if item already exists in cart
        $existingItem = CartItem::where([
            'user_id' => $request->user()->id,
            'product_id' => $request->product_id,
            'variant_id' => $request->variant_id,
        ])->first();

        if ($existingItem) {
            // Update quantity if item exists
            $existingItem->quantity += $request->quantity;
            $existingItem->save();
            $cartItem = $existingItem;
        } else {
            // Create new cart item
            $cartItem = CartItem::create([
                'user_id' => $request->user()->id,
                'product_id' => $request->product_id,
                'variant_id' => $request->variant_id,
                'quantity' => $request->quantity,
            ]);
        }

        // Load relationships for response
        $cartItem->load(['product.category', 'variant']);

        return response()->json([
            'success' => true,
            'message' => 'Item added to cart successfully',
            'data' => [
                'id' => $cartItem->id,
                'product_id' => $cartItem->product_id,
                'variant_id' => $cartItem->variant_id,
                'quantity' => $cartItem->quantity,
                'price' => $cartItem->getPrice(),
                'total_price' => $cartItem->getTotalPrice(),
                'product' => [
                    'id' => $cartItem->product->id,
                    'name' => $cartItem->product->name,
                    'image_url' => $cartItem->product->image_url,
                    'category' => $cartItem->product->category->name ?? null,
                ],
                'variant' => $cartItem->variant ? [
                    'id' => $cartItem->variant->id,
                    'type' => $cartItem->variant->type,
                    'value' => $cartItem->variant->value,
                    'unit' => $cartItem->variant->unit,
                    'price' => $cartItem->variant->price,
                ] : null,
            ]
        ], 201);
    }

    /**
     * Update cart item quantity
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cartItem = CartItem::where([
            'id' => $id,
            'user_id' => $request->user()->id,
        ])->firstOrFail();

        $cartItem->update([
            'quantity' => $request->quantity,
        ]);

        $cartItem->load(['product.category', 'variant']);

        return response()->json([
            'success' => true,
            'message' => 'Cart item updated successfully',
            'data' => [
                'id' => $cartItem->id,
                'product_id' => $cartItem->product_id,
                'variant_id' => $cartItem->variant_id,
                'quantity' => $cartItem->quantity,
                'price' => $cartItem->getPrice(),
                'total_price' => $cartItem->getTotalPrice(),
                'product' => [
                    'id' => $cartItem->product->id,
                    'name' => $cartItem->product->name,
                    'image_url' => $cartItem->product->image_url,
                    'category' => $cartItem->product->category->name ?? null,
                ],
                'variant' => $cartItem->variant ? [
                    'id' => $cartItem->variant->id,
                    'type' => $cartItem->variant->type,
                    'value' => $cartItem->variant->value,
                    'unit' => $cartItem->variant->unit,
                    'price' => $cartItem->variant->price,
                ] : null,
            ]
        ]);
    }

    /**
     * Remove item from cart
     */
    public function destroy(Request $request, $id)
    {
        $cartItem = CartItem::where([
            'id' => $id,
            'user_id' => $request->user()->id,
        ])->firstOrFail();

        $cartItem->delete();

        return response()->json([
            'success' => true,
            'message' => 'Item removed from cart successfully',
        ]);
    }

    /**
     * Clear all items from cart
     */
    public function clear(Request $request)
    {
        CartItem::where('user_id', $request->user()->id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Cart cleared successfully',
        ]);
    }

    /**
     * Get cart item count
     */
    public function count(Request $request)
    {
        $count = CartItem::where('user_id', $request->user()->id)
            ->sum('quantity');

        return response()->json([
            'success' => true,
            'data' => [
                'count' => $count
            ]
        ]);
    }
}
