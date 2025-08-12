<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Discount;
use App\Models\FlashSale;
use App\Models\Product;
use Illuminate\Http\Request;

class PromotionController extends Controller
{
    /**
     * Get all active discounts
     */
    public function getActiveDiscounts()
    {
        $discounts = Discount::with('products')
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            })
            ->whereRaw('(usage_limit IS NULL OR used_count < usage_limit)')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $discounts->map(function ($discount) {
                return [
                    'id' => $discount->id,
                    'name' => $discount->name,
                    'description' => $discount->description,
                    'type' => $discount->type,
                    'value' => $discount->value,
                    'min_quantity' => $discount->min_quantity,
                    'get_quantity' => $discount->get_quantity,
                    'min_purchase_amount' => $discount->min_purchase_amount,
                    'max_discount_amount' => $discount->max_discount_amount,
                    'products' => $discount->products->map(function ($product) {
                        return [
                            'id' => $product->id,
                            'name' => $product->name,
                            'price' => $product->price,
                        ];
                    }),
                    'starts_at' => $discount->starts_at,
                    'ends_at' => $discount->ends_at,
                ];
            })
        ]);
    }

    /**
     * Get all active flash sales
     */
    public function getActiveFlashSales()
    {
        $flashSales = FlashSale::with('products')
            ->where('is_active', true)
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now())
            ->whereRaw('(max_quantity IS NULL OR sold_quantity < max_quantity)')
            ->orderBy('ends_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $flashSales->map(function ($flashSale) {
                return [
                    'id' => $flashSale->id,
                    'title' => $flashSale->title,
                    'description' => $flashSale->description,
                    'discount_percentage' => $flashSale->discount_percentage,
                    'max_discount_amount' => $flashSale->max_discount_amount,
                    'remaining_quantity' => $flashSale->getRemainingQuantity(),
                    'progress_percentage' => $flashSale->getProgressPercentage(),
                    'products' => $flashSale->products->map(function ($product) use ($flashSale) {
                        $originalPrice = $product->price;
                        $discountAmount = $flashSale->calculateDiscount($originalPrice);
                        $finalPrice = $originalPrice - $discountAmount;
                        
                        return [
                            'id' => $product->id,
                            'name' => $product->name,
                            'original_price' => $originalPrice,
                            'discount_amount' => $discountAmount,
                            'final_price' => $finalPrice,
                            'image' => $product->image,
                        ];
                    }),
                    'starts_at' => $flashSale->starts_at,
                    'ends_at' => $flashSale->ends_at,
                ];
            })
        ]);
    }

    /**
     * Get product with all applicable promotions
     */
    public function getProductPromotions($productId)
    {
        $product = Product::with(['discounts', 'flashSales'])->find($productId);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        // Get active discounts for this product
        $activeDiscounts = $product->getActiveDiscounts();
        
        // Get active flash sales for this product
        $activeFlashSales = $product->getActiveFlashSales();

        // Calculate best price
        $originalPrice = $product->price;
        $bestDiscount = 0;
        $bestPromotionType = null;
        $bestPromotion = null;

        // Check regular discounts
        foreach ($activeDiscounts as $discount) {
            $discountAmount = $discount->calculateDiscount($originalPrice, 1);
            if ($discountAmount > $bestDiscount) {
                $bestDiscount = $discountAmount;
                $bestPromotionType = 'discount';
                $bestPromotion = $discount;
            }
        }

        // Check flash sales (usually higher priority)
        foreach ($activeFlashSales as $flashSale) {
            $discountAmount = $flashSale->calculateDiscount($originalPrice);
            if ($discountAmount > $bestDiscount) {
                $bestDiscount = $discountAmount;
                $bestPromotionType = 'flash_sale';
                $bestPromotion = $flashSale;
            }
        }

        $finalPrice = max(0, $originalPrice - $bestDiscount);

        return response()->json([
            'success' => true,
            'data' => [
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'original_price' => $originalPrice,
                    'final_price' => $finalPrice,
                    'discount_amount' => $bestDiscount,
                    'discount_percentage' => $originalPrice > 0 ? round(($bestDiscount / $originalPrice) * 100, 2) : 0,
                    'has_promotion' => $bestDiscount > 0,
                ],
                'active_discounts' => $activeDiscounts->map(function ($discount) use ($originalPrice) {
                    return [
                        'id' => $discount->id,
                        'name' => $discount->name,
                        'type' => $discount->type,
                        'discount_amount' => $discount->calculateDiscount($originalPrice, 1),
                    ];
                }),
                'active_flash_sales' => $activeFlashSales->map(function ($flashSale) use ($originalPrice) {
                    return [
                        'id' => $flashSale->id,
                        'title' => $flashSale->title,
                        'discount_percentage' => $flashSale->discount_percentage,
                        'discount_amount' => $flashSale->calculateDiscount($originalPrice),
                        'ends_at' => $flashSale->ends_at,
                        'remaining_quantity' => $flashSale->getRemainingQuantity(),
                    ];
                }),
                'best_promotion' => $bestPromotion ? [
                    'type' => $bestPromotionType,
                    'name' => $bestPromotionType === 'flash_sale' ? $bestPromotion->title : $bestPromotion->name,
                    'discount_amount' => $bestDiscount,
                ] : null,
            ]
        ]);
    }

    /**
     * Calculate cart total with all applicable discounts
     */
    public function calculateCartTotal(Request $request)
    {
        $cartItems = $request->input('items', []); // Array of {product_id, quantity}
        
        $total = 0;
        $totalDiscount = 0;
        $appliedPromotions = [];
        $itemBreakdown = [];

        foreach ($cartItems as $item) {
            $product = Product::find($item['product_id']);
            if (!$product) continue;

            $quantity = $item['quantity'];
            $originalPrice = $product->price;
            $itemTotal = $originalPrice * $quantity;

            // Get active discounts for this product
            $activeDiscounts = $product->getActiveDiscounts();
            $activeFlashSales = $product->getActiveFlashSales();

            $bestDiscount = 0;
            $bestPromotion = null;

            // Check regular discounts
            foreach ($activeDiscounts as $discount) {
                $discountAmount = $discount->calculateDiscount($originalPrice, $quantity);
                if ($discountAmount > $bestDiscount) {
                    $bestDiscount = $discountAmount;
                    $bestPromotion = [
                        'type' => 'discount',
                        'name' => $discount->name,
                        'id' => $discount->id,
                    ];
                }
            }

            // Check flash sales
            foreach ($activeFlashSales as $flashSale) {
                $discountAmount = $flashSale->calculateDiscount($originalPrice) * $quantity;
                if ($discountAmount > $bestDiscount) {
                    $bestDiscount = $discountAmount;
                    $bestPromotion = [
                        'type' => 'flash_sale',
                        'name' => $flashSale->title,
                        'id' => $flashSale->id,
                    ];
                }
            }

            $finalItemTotal = max(0, $itemTotal - $bestDiscount);

            $itemBreakdown[] = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'quantity' => $quantity,
                'unit_price' => $originalPrice,
                'subtotal' => $itemTotal,
                'discount_amount' => $bestDiscount,
                'final_total' => $finalItemTotal,
                'applied_promotion' => $bestPromotion,
            ];

            $total += $finalItemTotal;
            $totalDiscount += $bestDiscount;

            if ($bestPromotion) {
                $appliedPromotions[] = $bestPromotion;
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'subtotal' => $total + $totalDiscount,
                'total_discount' => $totalDiscount,
                'final_total' => $total,
                'applied_promotions' => $appliedPromotions,
                'item_breakdown' => $itemBreakdown,
            ]
        ]);
    }
}
