<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function index()
    {
        $favorites = Favorite::with(['user','product'])->latest()->paginate(20);
        return view('admin.favorites.index', compact('favorites'));
    }

    public function show(Favorite $favorite)
    {
        $favorite->load(['user','product']);
        return view('admin.favorites.show', compact('favorite'));
    }

    public function destroy(Favorite $favorite)
    {
        $favorite->delete();
        return redirect()->route('admin.favorites.index')->with('status','Favorite removed');
    }

    public function analytics()
    {
        // Simple placeholder analytics
        $top = Favorite::selectRaw('product_id, COUNT(*) as cnt')->groupBy('product_id')->orderByDesc('cnt')->limit(10)->get();
        return view('admin.favorites.analytics', compact('top'));
    }
}
