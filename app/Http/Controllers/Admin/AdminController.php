<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use App\Models\Order;
use App\Models\Discount;
use App\Models\FlashSale;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'products' => Product::count(),
            'categories' => Category::count(),
            'users' => User::count(),
            'orders' => Order::count(),
            'discounts' => Discount::count(),
            'flash_sales' => FlashSale::count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
