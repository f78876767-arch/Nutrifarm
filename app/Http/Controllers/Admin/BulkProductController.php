<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProductsExport;
use App\Exports\BulkProductTemplateExport;
use App\Imports\BulkProductsImport;

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
                
                $products = Product::with('variants')->whereIn('id', $productIds)->get();
                foreach ($products as $product) {
                    // Update all variants for each product
                    foreach ($product->variants as $variant) {
                        $newPrice = $this->calculateNewPrice(
                            $variant->base_price, 
                            $request->price_action,
                            $request->price_value,
                            $request->price_type
                        );
                        $variant->update(['base_price' => $newPrice]);
                    }
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
        return Excel::download(new BulkProductTemplateExport, 'bulk_products_template.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv,xls',
            'images_zip' => 'nullable|file|mimes:zip'
        ]);

        $tempExtractPath = null;
        if ($request->hasFile('images_zip')) {
            $zipFile = $request->file('images_zip');
            $tempDir = storage_path('app/tmp/bulk_images_' . time());
            if (!is_dir($tempDir)) { @mkdir($tempDir, 0775, true); }
            $zip = new \ZipArchive();
            if ($zip->open($zipFile->getRealPath()) === true) {
                $zip->extractTo($tempDir);
                $zip->close();
                $tempExtractPath = $tempDir;
            } else {
                return back()->with('error', 'ZIP gambar gagal dibuka');
            }
        }

        try {
            $importer = new BulkProductsImport($tempExtractPath);
            Excel::import($importer, $request->file('file'));
            $errors = $importer->errors();
            if ($errors) {
                return back()->with('warning', 'Import selesai dengan beberapa error')->with('import_errors', $errors);
            }
            return back()->with('success', 'Import produk berhasil');
        } catch (\Throwable $e) {
            return back()->with('error', 'Import gagal: ' . $e->getMessage());
        } finally {
            if ($tempExtractPath && is_dir($tempExtractPath)) {
                $this->deleteDir($tempExtractPath);
            }
        }
    }

    private function deleteDir($dir)
    {
        if (!is_dir($dir)) return;
        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) { $this->deleteDir($path); } else { @unlink($path); }
        }
        @rmdir($dir);
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