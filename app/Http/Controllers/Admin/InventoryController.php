<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\StockMovement;
use App\Models\Variant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function index()
    {
        $products = Product::with(['category', 'variants'])
            ->withCount('variants')
            ->latest()
            ->paginate(20);
        
        // Compute low/out of stock based on derived total variant stock per product
        $allProducts = Product::with('variants')->get();
        $lowStockCount = $allProducts->filter(fn($p) => $p->total_stock <= 10 && $p->total_stock > 0)->count();
        $outOfStockCount = $allProducts->filter(fn($p) => $p->total_stock === 0)->count();
        
        // Variant-level stats using canonical stock_quantity
        $lowStockVariantsCount = Variant::where('stock_quantity', '<=', 10)->where('stock_quantity', '>', 0)->count();
        $outOfStockVariantsCount = Variant::where('stock_quantity', 0)->count();
        
        return view('admin.inventory.index', compact(
            'products', 
            'lowStockCount', 
            'outOfStockCount',
            'lowStockVariantsCount',
            'outOfStockVariantsCount'
        ));
    }

    public function alerts()
    {
        // Build product alert lists using derived total variant stock
        $products = Product::with(['category', 'variants'])->get();
        
        $lowStockProducts = $products->filter(function ($product) {
            return $product->total_stock <= 10 && $product->total_stock > 0;
        })->values();
            
        $outOfStockProducts = $products->filter(function ($product) {
            return $product->total_stock === 0;
        })->values();

        return view('admin.inventory.alerts', compact('lowStockProducts', 'outOfStockProducts'));
    }

    public function movements()
    {
        $movements = StockMovement::with(['product', 'user'])
            ->latest()
            ->paginate(20);

        return view('admin.inventory.movements', compact('movements'));
    }

    public function reports()
    {
        $totalProducts = Product::count();
        $totalVariants = Variant::count();
        
        // Total stock units are variant stock quantities (source of truth)
        $totalStock = (int) Variant::sum('stock_quantity');
        
        // Calculate stock value from variants only
        $variantStockValue = (float) DB::table('variants')
            ->selectRaw('SUM(COALESCE(base_price, 0) * COALESCE(stock_quantity, 0)) as total')
            ->value('total');
        $stockValue = $variantStockValue;
        
        $categoryStock = Category::with(['products.variants'])
            ->get()
            ->map(function ($category) {
                $variantsCount = $category->products->sum(fn($product) => $product->variants->count());
                $totalVariantStock = $category->products->sum(function ($product) {
                    return $product->variants->sum('stock_quantity');
                });
                $variantStockValue = $category->products->sum(function ($product) {
                    return $product->variants->sum(function ($variant) {
                        $price = $variant->base_price ?? 0;
                        $qty = $variant->stock_quantity ?? 0;
                        return $price * $qty;
                    });
                });
                
                return [
                    'name' => $category->name,
                    'products_count' => $category->products->count(),
                    'variants_count' => $variantsCount,
                    // For compatibility with the view structure, set both keys using variant totals
                    'total_stock' => $totalVariantStock,
                    'total_variant_stock' => $totalVariantStock,
                    'stock_value' => $variantStockValue,
                ];
            });

        return view('admin.inventory.reports', compact(
            'totalProducts', 'totalVariants', 'totalStock', 'stockValue', 'categoryStock'
        ));
    }

    public function adjustStock(Request $request, Product $product)
    {
        // Note: Product-level stock is derived; direct adjustments are deprecated.
        // This endpoint is kept for backward compatibility but will no-op to prevent inconsistent state.
        return redirect()->back()->with('error', 'Direct product stock adjustment is disabled. Please adjust stock per variant on the product page.');
    }

    public function bulkUpdate(Request $request)
    {
        // Deprecated: product-level bulk stock updates are disabled in variant-only mode.
        return redirect()->back()->with('error', 'Bulk product stock update is disabled. Manage stock per variant.');
    }

    public function export()
    {
        $products = Product::with(['category', 'variants'])->get();
        
        $csvData = [];
        $csvData[] = ['Product Name', 'SKU', 'Category', 'Total Stock (Variants)', 'Variant Stock Value'];
        
        foreach ($products as $product) {
            $totalStock = (int) $product->total_stock;
            $variantValue = $product->variants->sum(function ($variant) {
                $price = $variant->base_price ?? 0;
                $qty = $variant->stock_quantity ?? 0;
                return $price * $qty;
            });

            $csvData[] = [
                $product->name,
                $product->sku,
                $product->category->name ?? 'N/A',
                $totalStock,
                $variantValue,
            ];
        }

        $filename = 'inventory_report_' . date('Y-m-d') . '.csv';
        
        $handle = fopen('php://output', 'w');
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        foreach ($csvData as $row) {
            fputcsv($handle, $row);
        }
        
        fclose($handle);
        exit;
    }
}