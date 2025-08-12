<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Order;
use App\Models\User;
use App\Models\Category;
use App\Models\Discount;
use App\Models\FlashSale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    public function index()
    {
        // Date range for analytics
        $startDate = Carbon::now()->subDays(30);
        $endDate = Carbon::now();
        
        // Revenue Analytics
        $revenueData = $this->getRevenueAnalytics($startDate, $endDate);
        
        // Product Analytics
        $productData = $this->getProductAnalytics();
        
        // Customer Analytics
        $customerData = $this->getCustomerAnalytics($startDate, $endDate);
        
        // Sales Performance
        $salesData = $this->getSalesPerformance($startDate, $endDate);
        
        // Category Performance
        $categoryData = $this->getCategoryPerformance($startDate, $endDate);
        
        return view('admin.analytics.index', compact(
            'revenueData',
            'productData', 
            'customerData',
            'salesData',
            'categoryData'
        ));
    }
    
    public function revenue(Request $request)
    {
        $period = $request->get('period', '30days');
        $startDate = $this->getPeriodStartDate($period);
        $endDate = Carbon::now();
        
        $dailyRevenue = Order::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('SUM(total_amount) as revenue'),
            DB::raw('COUNT(*) as orders')
        )
        ->where('status', 'completed')
        ->whereBetween('created_at', [$startDate, $endDate])
        ->groupBy('date')
        ->orderBy('date')
        ->get();
        
        $totalRevenue = $dailyRevenue->sum('revenue');
        $totalOrders = $dailyRevenue->sum('orders');
        $avgOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;
        
        return view('admin.analytics.revenue', compact(
            'dailyRevenue', 
            'totalRevenue', 
            'totalOrders', 
            'avgOrderValue',
            'period'
        ));
    }
    
    public function products()
    {
        // Best selling products
        $bestSellers = Product::select('products.*', DB::raw('SUM(order_products.quantity) as total_sold'))
            ->join('order_products', 'products.id', '=', 'order_products.product_id')
            ->join('orders', 'order_products.order_id', '=', 'orders.id')
            ->where('orders.status', 'completed')
            ->groupBy('products.id')
            ->orderByDesc('total_sold')
            ->limit(10)
            ->get();
            
        // Low stock products
        $lowStock = Product::where('stock_quantity', '<', 10)
            ->where('stock_quantity', '>', 0)
            ->orderBy('stock_quantity')
            ->get();
            
        // Out of stock products
        $outOfStock = Product::where('stock_quantity', '<=', 0)->get();
        
        // Most viewed products (you'd need to implement view tracking)
        $mostViewed = Product::orderByDesc('created_at')->limit(10)->get();
        
        return view('admin.analytics.products', compact(
            'bestSellers',
            'lowStock', 
            'outOfStock',
            'mostViewed'
        ));
    }
    
    public function customers()
    {
        // Top customers by spending
        $topCustomers = User::select('users.*', DB::raw('SUM(orders.total_amount) as total_spent'))
            ->join('orders', 'users.id', '=', 'orders.user_id')
            ->where('orders.status', 'completed')
            ->groupBy('users.id')
            ->orderByDesc('total_spent')
            ->limit(10)
            ->get();
            
        // Customer acquisition
        $newCustomers = User::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as count')
        )
        ->where('created_at', '>=', Carbon::now()->subDays(30))
        ->groupBy('date')
        ->orderBy('date')
        ->get();
        
        // Customer retention metrics
        $returningCustomers = User::whereHas('orders', function($q) {
            $q->where('created_at', '>=', Carbon::now()->subDays(30));
        })
        ->withCount(['orders' => function($q) {
            $q->where('created_at', '>=', Carbon::now()->subDays(30));
        }])
        ->having('orders_count', '>', 1)
        ->count();
        
        return view('admin.analytics.customers', compact(
            'topCustomers',
            'newCustomers',
            'returningCustomers'
        ));
    }
    
    private function getRevenueAnalytics($startDate, $endDate)
    {
        return [
            'total_revenue' => Order::where('status', 'completed')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('total_amount'),
            'total_orders' => Order::whereBetween('created_at', [$startDate, $endDate])->count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'completed_orders' => Order::where('status', 'completed')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count(),
        ];
    }
    
    private function getProductAnalytics()
    {
        return [
            'total_products' => Product::count(),
            'active_products' => Product::where('is_active', true)->count(),
            'low_stock_products' => Product::where('stock_quantity', '<', 10)->count(),
            'out_of_stock_products' => Product::where('stock_quantity', '<=', 0)->count(),
        ];
    }
    
    private function getCustomerAnalytics($startDate, $endDate)
    {
        return [
            'total_customers' => User::count(),
            'new_customers' => User::whereBetween('created_at', [$startDate, $endDate])->count(),
            'active_customers' => User::whereHas('orders', function($q) use ($startDate, $endDate) {
                $q->whereBetween('created_at', [$startDate, $endDate]);
            })->count(),
        ];
    }
    
    private function getSalesPerformance($startDate, $endDate)
    {
        return Order::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('SUM(total_amount) as revenue'),
            DB::raw('COUNT(*) as orders')
        )
        ->where('status', 'completed')
        ->whereBetween('created_at', [$startDate, $endDate])
        ->groupBy('date')
        ->orderBy('date')
        ->get();
    }
    
    private function getCategoryPerformance($startDate, $endDate)
    {
        return Category::select('categories.*', 
            DB::raw('SUM(order_products.quantity * products.price) as revenue'),
            DB::raw('SUM(order_products.quantity) as items_sold')
        )
        ->join('category_product', 'categories.id', '=', 'category_product.category_id')
        ->join('products', 'category_product.product_id', '=', 'products.id')
        ->join('order_products', 'products.id', '=', 'order_products.product_id')
        ->join('orders', 'order_products.order_id', '=', 'orders.id')
        ->where('orders.status', 'completed')
        ->whereBetween('orders.created_at', [$startDate, $endDate])
        ->groupBy('categories.id')
        ->orderByDesc('revenue')
        ->get();
    }
    
    private function getPeriodStartDate($period)
    {
        switch ($period) {
            case '7days':
                return Carbon::now()->subDays(7);
            case '30days':
                return Carbon::now()->subDays(30);
            case '3months':
                return Carbon::now()->subMonths(3);
            case '1year':
                return Carbon::now()->subYear();
            default:
                return Carbon::now()->subDays(30);
        }
    }
}
