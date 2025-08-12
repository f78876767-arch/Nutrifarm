<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    /**
     * Get user's favorite products
     */
    public function index(Request $request)
    {
        $favorites = $request->user()->favorites()
            ->with(['product.category', 'product.variants'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $favorites->map(function ($favorite) {
                return [
                    'id' => $favorite->id,
                    'product_id' => $favorite->product_id,
                    'created_at' => $favorite->created_at,
                    'product' => [
                        'id' => $favorite->product->id,
                        'name' => $favorite->product->name,
                        'description' => $favorite->product->description,
                        'price' => $favorite->product->price,
                        'discount_price' => $favorite->product->discount_price,
                        'image_url' => $favorite->product->image_url,
                        'is_active' => $favorite->product->is_active,
                        'category' => $favorite->product->category ? [
                            'id' => $favorite->product->category->id,
                            'name' => $favorite->product->category->name,
                        ] : null,
                        'variants' => $favorite->product->variants->map(function ($variant) {
                            return [
                                'id' => $variant->id,
                                'type' => $variant->type,
                                'value' => $variant->value,
                                'unit' => $variant->unit,
                                'price' => $variant->price,
                            ];
                        }),
                    ],
                ];
            })
        ]);
    }

    /**
     * Add product to favorites
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        // Check if already in favorites
        $existing = Favorite::where([
            'user_id' => $request->user()->id,
            'product_id' => $request->product_id,
        ])->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Product is already in favorites',
            ], 409);
        }

        $favorite = Favorite::create([
            'user_id' => $request->user()->id,
            'product_id' => $request->product_id,
        ]);

        $favorite->load(['product.category']);

        return response()->json([
            'success' => true,
            'message' => 'Product added to favorites successfully',
            'data' => [
                'id' => $favorite->id,
                'product_id' => $favorite->product_id,
                'created_at' => $favorite->created_at,
                'product' => [
                    'id' => $favorite->product->id,
                    'name' => $favorite->product->name,
                    'price' => $favorite->product->price,
                    'discount_price' => $favorite->product->discount_price,
                    'image_url' => $favorite->product->image_url,
                    'category' => $favorite->product->category->name ?? null,
                ],
            ]
        ], 201);
    }

    /**
     * Remove product from favorites
     */
    public function destroy(Request $request, $id)
    {
        $favorite = Favorite::where([
            'id' => $id,
            'user_id' => $request->user()->id,
        ])->firstOrFail();

        $favorite->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product removed from favorites successfully',
        ]);
    }

    /**
     * Check if product is in favorites
     */
    public function check(Request $request, $productId)
    {
        $isFavorite = Favorite::where([
            'user_id' => $request->user()->id,
            'product_id' => $productId,
        ])->exists();

        return response()->json([
            'success' => true,
            'data' => [
                'is_favorite' => $isFavorite
            ]
        ]);
    }

    /**
     * Toggle product in favorites
     */
    public function toggle(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $favorite = Favorite::where([
            'user_id' => $request->user()->id,
            'product_id' => $request->product_id,
        ])->first();

        if ($favorite) {
            // Remove from favorites
            $favorite->delete();
            return response()->json([
                'success' => true,
                'message' => 'Product removed from favorites',
                'data' => [
                    'is_favorite' => false
                ]
            ]);
        } else {
            // Add to favorites
            $favorite = Favorite::create([
                'user_id' => $request->user()->id,
                'product_id' => $request->product_id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Product added to favorites',
                'data' => [
                    'is_favorite' => true,
                    'favorite_id' => $favorite->id,
                ]
            ], 201);
        }
    }
}
