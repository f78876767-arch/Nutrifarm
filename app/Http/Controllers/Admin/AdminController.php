<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use App\Models\Order;
use App\Models\Discount;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard()
    {
        // Active discounts: status + schedule + usage limit
        $activeDiscounts = Discount::where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->where(function ($q) {
                $q->whereNull('usage_limit')->orWhereColumn('used_count', '<', 'usage_limit');
            })
            ->count();

        // Revenue today: sum of totals for paid orders today
        $revenueToday = (float) Order::where('payment_status', 'paid')
            ->whereDate('paid_at', now()->toDateString())
            ->sum('total');

        $stats = [
            'products' => Product::count(),
            'categories' => Category::count(),
            'users' => User::count(),
            'orders' => Order::count(),
            'discounts' => $activeDiscounts,
            'revenue_today' => $revenueToday,
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
