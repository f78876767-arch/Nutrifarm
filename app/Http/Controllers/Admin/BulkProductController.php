<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProductsExport;
use App\Imports\ProductsImport;

class BulkProductController extends Controller
{
    public function index()
    {
        $products = Product::with(['category', 'variants'])
            ->withCount('variants')
            ->latest()
            ->paginate(20);
        
        $categories = Category::all();
        
        return view('admin.bulk-products.index', compact('products', 'categories'));
    }

    public function bulkEdit(Request $request)
    {
        $request->validate([
            'product_ids' => 'required|array',
            'product_ids.*' => 'exists:products,id',
            'action' => 'required|in:update_category,update_status,update_price,delete'
        ]);

        $productIds = $request->product_ids;
        
        switch ($request->action) {
            case 'update_category':
                $request->validate(['category_id' => 'required|exists:categories,id']);
                Product::whereIn('id', $productIds)
                    ->update(['category_id' => $request->category_id]);
                break;
                
            case 'update_status':
                $request->validate(['status' => 'required|in:active,inactive']);
                Product::whereIn('id', $productIds)
                    ->update(['status' => $request->status]);
                break;
                
            case 'update_price':
                $request->validate([
                    'price_action' => 'required|in:increase,decrease,set',
                    'price_value' => 'required|numeric|min:0',
                    'price_type' => 'required|in:percentage,fixed'
                ]);
                
                $products = Product::whereIn('id', $productIds)->get();
                foreach ($products as $product) {
                    $newPrice = $this->calculateNewPrice(
                        $product->price, 
                        $request->price_action,
                        $request->price_value,
                        $request->price_type
                    );
                    $product->update(['price' => $newPrice]);
                }
                break;
                
            case 'delete':
                Product::whereIn('id', $productIds)->delete();
                break;
        }

        return redirect()->back()->with('success', 'Bulk action completed successfully');
    }

    public function export()
    {
        return Excel::download(new ProductsExport, 'products.xlsx');
    }

    public function importTemplate()
    {
        $templatePath = resource_path('templates/products_import_template.xlsx');
        
        if (!file_exists($templatePath)) {
            // Create a basic template
            $headers = [
                'Name', 'Description', 'Price', 'Stock Quantity', 'Category', 
                'SKU', 'Status', 'Weight', 'Image URL'
            ];
            
            // You can create a simple CSV template here
            $content = implode(',', $headers);
            return response($content)
                ->header('Content-Type', 'text/csv')
                ->header('Content-Disposition', 'attachment; filename="products_import_template.csv"');
        }
        
        return response()->download($templatePath);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv,xls'
        ]);

        try {
            Excel::import(new ProductsImport, $request->file('file'));
            return redirect()->back()->with('success', 'Products imported successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    private function calculateNewPrice($currentPrice, $action, $value, $type)
    {
        if ($action === 'set') {
            return $value;
        }

        $change = $type === 'percentage' ? ($currentPrice * $value / 100) : $value;
        
        if ($action === 'increase') {
            return $currentPrice + $change;
        } else {
            return max(0, $currentPrice - $change);
        }
    }
}