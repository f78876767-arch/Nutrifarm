<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Category;
use App\Models\StockMovement;
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
        
        // Include variants in low stock calculation
        $lowStockCount = Product::where('stock_quantity', '<=', 10)->count();
        $lowStockVariantsCount = \App\Models\Variant::where('stock', '<=', 10)->where('stock', '>', 0)->count();
        
        $outOfStockCount = Product::where('stock_quantity', 0)->count();
        $outOfStockVariantsCount = \App\Models\Variant::where('stock', 0)->count();
        
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
        $lowStockProducts = Product::with('category')
            ->where('stock_quantity', '<=', 10)
            ->where('stock_quantity', '>', 0)
            ->get();
            
        $outOfStockProducts = Product::with('category')
            ->where('stock_quantity', 0)
            ->get();

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
        $totalVariants = \App\Models\Variant::count();
        
        $totalStock = Product::sum('stock_quantity');
        $totalVariantStock = \App\Models\Variant::sum('stock');
        
        // Calculate stock value including variants
        $productStockValue = Product::sum(DB::raw('price * stock_quantity'));
        $variantStockValue = \App\Models\Variant::sum(DB::raw('COALESCE(price, 0) * COALESCE(stock, 0)'));
        $stockValue = $productStockValue + $variantStockValue;
        
        $categoryStock = Category::with(['products.variants'])
            ->get()
            ->map(function ($category) {
                $productStockValue = $category->products->sum(function ($product) {
                    return $product->price * $product->stock_quantity;
                });
                
                $variantStockValue = $category->products->sum(function ($product) {
                    return $product->variants->sum(function ($variant) {
                        return ($variant->price ?: 0) * ($variant->stock ?: 0);
                    });
                });
                
                return [
                    'name' => $category->name,
                    'products_count' => $category->products->count(),
                    'variants_count' => $category->products->sum(function ($product) {
                        return $product->variants->count();
                    }),
                    'total_stock' => $category->products->sum('stock_quantity'),
                    'total_variant_stock' => $category->products->sum(function ($product) {
                        return $product->variants->sum('stock');
                    }),
                    'stock_value' => $productStockValue + $variantStockValue
                ];
            });

        return view('admin.inventory.reports', compact(
            'totalProducts', 'totalVariants', 'totalStock', 'totalVariantStock', 'stockValue', 'categoryStock'
        ));
    }

    public function adjustStock(Request $request, Product $product)
    {
        $request->validate([
            'adjustment_type' => 'required|in:add,subtract,set',
            'quantity' => 'required|integer|min:0',
            'reason' => 'nullable|string|max:255'
        ]);

        $oldQuantity = $product->stock_quantity;
        
        switch ($request->adjustment_type) {
            case 'add':
                $newQuantity = $oldQuantity + $request->quantity;
                break;
            case 'subtract':
                $newQuantity = max(0, $oldQuantity - $request->quantity);
                break;
            case 'set':
                $newQuantity = $request->quantity;
                break;
        }

        $product->update(['stock_quantity' => $newQuantity]);

        // Record stock movement
        StockMovement::create([
            'product_id' => $product->id,
            'user_id' => auth()->id(),
            'type' => $request->adjustment_type,
            'quantity' => $request->quantity,
            'previous_quantity' => $oldQuantity,
            'new_quantity' => $newQuantity,
            'reason' => $request->reason
        ]);

        return redirect()->back()->with('success', 'Stock adjusted successfully');
    }

    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'products' => 'required|array',
            'products.*.id' => 'required|exists:products,id',
            'products.*.stock_quantity' => 'required|integer|min:0'
        ]);

        foreach ($request->products as $productData) {
            $product = Product::find($productData['id']);
            $oldQuantity = $product->stock_quantity;
            $newQuantity = $productData['stock_quantity'];
            
            $product->update(['stock_quantity' => $newQuantity]);

            // Record stock movement
            StockMovement::create([
                'product_id' => $product->id,
                'user_id' => auth()->id(),
                'type' => 'bulk_update',
                'quantity' => abs($newQuantity - $oldQuantity),
                'previous_quantity' => $oldQuantity,
                'new_quantity' => $newQuantity,
                'reason' => 'Bulk stock update'
            ]);
        }

        return redirect()->back()->with('success', 'Stock quantities updated successfully');
    }

    public function export()
    {
        $products = Product::with('category')->get();
        
        $csvData = [];
        $csvData[] = ['Product Name', 'SKU', 'Category', 'Stock Quantity', 'Price', 'Stock Value'];
        
        foreach ($products as $product) {
            $csvData[] = [
                $product->name,
                $product->sku,
                $product->category->name ?? 'N/A',
                $product->stock_quantity,
                $product->price,
                $product->price * $product->stock_quantity
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