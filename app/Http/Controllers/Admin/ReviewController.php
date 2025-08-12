<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $query = Review::with(['user', 'product']);

        // Filters
        if ($request->filled('rating')) {
            $query->where('rating', $request->rating);
        }

        if ($request->filled('status')) {
            if ($request->status === 'approved') {
                $query->where('is_approved', true);
            } elseif ($request->status === 'pending') {
                $query->where('is_approved', false);
            }
        }

        if ($request->filled('verified')) {
            $query->where('is_verified_purchase', $request->verified === 'yes');
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'LIKE', '%' . $request->search . '%')
                  ->orWhere('comment', 'LIKE', '%' . $request->search . '%')
                  ->orWhereHas('user', function($q2) use ($request) {
                      $q2->where('name', 'LIKE', '%' . $request->search . '%');
                  })
                  ->orWhereHas('product', function($q2) use ($request) {
                      $q2->where('name', 'LIKE', '%' . $request->search . '%');
                  });
            });
        }

        $reviews = $query->latest()->paginate(20);

        // Analytics
        $analytics = [
            'total_reviews' => Review::count(),
            'approved_reviews' => Review::where('is_approved', true)->count(),
            'pending_reviews' => Review::where('is_approved', false)->count(),
            'average_rating' => Review::approved()->avg('rating'),
            'rating_distribution' => Review::approved()
                ->selectRaw('rating, COUNT(*) as count')
                ->groupBy('rating')
                ->orderBy('rating', 'desc')
                ->pluck('count', 'rating')
                ->toArray(),
        ];

        return view('admin.reviews.index', compact('reviews', 'analytics'));
    }

    public function show(Review $review)
    {
        $review->load(['user', 'product', 'order']);
        return view('admin.reviews.show', compact('review'));
    }

    public function approve(Review $review)
    {
        $review->update(['is_approved' => true]);
        return back()->with('success', 'Review approved successfully.');
    }

    public function reject(Review $review)
    {
        $review->update(['is_approved' => false]);
        return back()->with('success', 'Review rejected successfully.');
    }

    public function respond(Request $request, Review $review)
    {
        $request->validate([
            'admin_response' => 'required|string|max:1000'
        ]);

        $review->update([
            'admin_response' => $request->admin_response,
            'admin_response_at' => now()
        ]);

        return back()->with('success', 'Response added successfully.');
    }

    public function destroy(Review $review)
    {
        // Delete images if they exist
        if ($review->images) {
            foreach ($review->images as $image) {
                Storage::disk('public')->delete($image);
            }
        }

        $review->delete();
        return redirect()->route('admin.reviews.index')->with('success', 'Review deleted successfully.');
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:approve,reject,delete',
            'reviews' => 'required|array',
            'reviews.*' => 'exists:reviews,id'
        ]);

        $reviews = Review::whereIn('id', $request->reviews);

        switch ($request->action) {
            case 'approve':
                $reviews->update(['is_approved' => true]);
                $message = 'Reviews approved successfully.';
                break;
            case 'reject':
                $reviews->update(['is_approved' => false]);
                $message = 'Reviews rejected successfully.';
                break;
            case 'delete':
                // Delete associated images
                $reviewsToDelete = $reviews->get();
                foreach ($reviewsToDelete as $review) {
                    if ($review->images) {
                        foreach ($review->images as $image) {
                            Storage::disk('public')->delete($image);
                        }
                    }
                }
                $reviews->delete();
                $message = 'Reviews deleted successfully.';
                break;
        }

        return back()->with('success', $message);
    }

    public function analytics()
    {
        $analytics = [
            'overview' => [
                'total_reviews' => Review::count(),
                'approved_reviews' => Review::where('is_approved', true)->count(),
                'pending_reviews' => Review::where('is_approved', false)->count(),
                'verified_purchases' => Review::where('is_verified_purchase', true)->count(),
            ],
            'rating_stats' => [
                'average_rating' => Review::approved()->avg('rating'),
                'rating_distribution' => Review::approved()
                    ->selectRaw('rating, COUNT(*) as count')
                    ->groupBy('rating')
                    ->orderBy('rating', 'desc')
                    ->pluck('count', 'rating')
                    ->toArray(),
            ],
            'top_products' => Product::withCount(['reviews as approved_reviews_count' => function($query) {
                    $query->where('is_approved', true);
                }])
                ->withAvg(['reviews as average_rating' => function($query) {
                    $query->where('is_approved', true);
                }], 'rating')
                ->having('approved_reviews_count', '>', 0)
                ->orderBy('approved_reviews_count', 'desc')
                ->limit(10)
                ->get(),
            'recent_activity' => Review::with(['user', 'product'])
                ->latest()
                ->limit(10)
                ->get(),
        ];

        return view('admin.reviews.analytics', compact('analytics'));
    }
}
