<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    // Create or update a review for an order item
    public function upsert(Request $request)
    {
        $data = $request->validate([
            'order_id' => 'required|integer|exists:orders,id',
            'order_product_id' => 'required|integer|exists:order_product,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:2000',
        ]);

        $user = Auth::user();
        $order = Order::with('user')->findOrFail($data['order_id']);
        if (!$order->isReviewableBy($user)) {
            return response()->json(['message' => 'Order not eligible for review'], 422);
        }

        $op = OrderProduct::with(['product','variant','order'])->findOrFail($data['order_product_id']);
        if ($op->order_id !== $order->id || $order->user_id !== $user->id) {
            return response()->json(['message' => 'Invalid order item'], 422);
        }

        $review = Review::updateOrCreate(
            ['order_product_id' => $op->id],
            [
                'user_id' => $user->id,
                'order_id' => $order->id,
                'product_id' => $op->product_id,
                'variant_id' => $op->variant_id,
                'rating' => $data['rating'],
                'comment' => $data['comment'] ?? null,
                'is_approved' => true,
            ]
        );

        return response()->json(['success' => true, 'review' => $review->fresh()]);
    }

    // Get reviews for a product (public)
    public function productReviews(Request $request, int $productId)
    {
        $perPage = max(1, (int) $request->query('per_page', 10));
        $reviews = Review::with(['user:id,name'])
            ->where('product_id', $productId)
            ->where('is_approved', true)
            ->orderByDesc('created_at')
            ->paginate($perPage);

        return response()->json($reviews);
    }
}
