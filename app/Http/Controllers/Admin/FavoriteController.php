<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use App\Models\User;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FavoriteController extends Controller
{
    public function index(Request $request)
    {
        $query = Favorite::with(['user', 'product']);
        
        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
            })->orWhereHas('product', function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%');
            });
        }
        
        // Filter by user
        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }
        
        $favorites = $query->latest()->paginate(15);
        $users = User::orderBy('name')->get();
        
        // Get statistics
        $stats = [
            'total_favorites' => Favorite::count(),
            'unique_users' => Favorite::distinct('user_id')->count(),
            'most_favorited_products' => Product::withCount('favorites')
                ->orderByDesc('favorites_count')
                ->limit(5)
                ->get(),
        ];
        
        return view('admin.favorites.index', compact('favorites', 'users', 'stats'));
    }

    public function show(Favorite $favorite)
    {
        $favorite->load(['user', 'product.categories']);
        return view('admin.favorites.show', compact('favorite'));
    }

    public function destroy(Favorite $favorite)
    {
        $favorite->delete();
        return redirect()->route('admin.favorites.index')->with('success', 'Favorite removed successfully');
    }

    /**
     * Get analytics data for favorites
     */
    public function analytics()
    {
        $monthlyFavorites = Favorite::select(
            DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
            DB::raw('COUNT(*) as count')
        )
        ->where('created_at', '>=', now()->subMonths(12))
        ->groupBy('month')
        ->orderBy('month')
        ->get();

        $topProducts = Product::withCount('favorites')
            ->orderByDesc('favorites_count')
            ->limit(10)
            ->get();

        $topUsers = User::withCount('favorites')
            ->orderByDesc('favorites_count')
            ->limit(10)
            ->get();

        return view('admin.favorites.analytics', compact('monthlyFavorites', 'topProducts', 'topUsers'));
    }
}
