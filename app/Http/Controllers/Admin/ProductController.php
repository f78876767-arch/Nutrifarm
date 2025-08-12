<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Variant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('categories')->paginate(15);
        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::all();
        $units = ['kg', 'g', 'l', 'ml', 'pcs', 'pack'];
        return view('admin.products.create', compact('categories', 'units'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'sku' => 'nullable|string|max:100|unique:products,sku',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'image_path' => 'nullable|image|max:2048',
            'categories' => 'array',
            'variants.*.name' => 'required_with:variants.*.value|string|max:255',
            'variants.*.value' => 'required_with:variants.*.name|string|max:255',
            'variants.*.unit' => 'nullable|string|max:32',
            'variants.*.price' => 'nullable|numeric|min:0',
            'variants.*.stock' => 'nullable|integer|min:0',
        ]);

        $productData = $request->only(['name', 'description', 'price', 'stock_quantity', 'sku']);
        $productData['is_active'] = $request->has('is_active');
        $productData['is_featured'] = $request->has('is_featured');

        $product = Product::create($productData);

        if ($request->hasFile('image_path')) {
            $path = $request->file('image_path')->store('products', 'public');
            $product->update(['image_path' => $path]);
        }

        if ($request->has('categories')) {
            $product->categories()->sync($request->categories);
        }

        // Create variants if provided
        if ($request->has('variants')) {
            foreach ($request->variants as $variantData) {
                if (!empty($variantData['name']) && !empty($variantData['value'])) {
                    $product->variants()->create([
                        'name' => $variantData['name'],
                        'value' => $variantData['value'],
                        'unit' => $variantData['unit'] ?? null,
                        'price' => $variantData['price'] ?? null,
                        'stock' => $variantData['stock'] ?? null,
                    ]);
                }
            }
        }

        return redirect()->route('admin.products.index')->with('success', 'Product created successfully');
    }

    public function show(Product $product)
    {
        $product->load('categories', 'variants');
        return view('admin.products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $categories = Category::all();
        $units = ['kg', 'g', 'l', 'ml', 'pcs', 'pack'];
        $product->load('categories', 'variants');
        return view('admin.products.edit', compact('product', 'categories', 'units'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'sku' => 'nullable|string|max:100|unique:products,sku,' . $product->id,
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'image_path' => 'nullable|image|max:2048',
            'categories' => 'array',
            'variants.*.name' => 'required_with:variants.*.value|string|max:255',
            'variants.*.value' => 'required_with:variants.*.name|string|max:255',
            'variants.*.unit' => 'nullable|string|max:32',
            'variants.*.price' => 'nullable|numeric|min:0',
            'variants.*.stock' => 'nullable|integer|min:0',
        ]);

        $productData = $request->only(['name', 'description', 'price', 'stock_quantity', 'sku']);
        $productData['is_active'] = $request->has('is_active');
        $productData['is_featured'] = $request->has('is_featured');

        $product->update($productData);

        if ($request->hasFile('image_path')) {
            // Delete old image if exists
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }
            $path = $request->file('image_path')->store('products', 'public');
            $product->update(['image_path' => $path]);
        }

        if ($request->has('categories')) {
            $product->categories()->sync($request->categories);
        }

        // Update variants
        $product->variants()->delete(); // Remove existing variants
        if ($request->has('variants')) {
            foreach ($request->variants as $variantData) {
                if (!empty($variantData['name']) && !empty($variantData['value'])) {
                    $product->variants()->create([
                        'name' => $variantData['name'],
                        'value' => $variantData['value'],
                        'unit' => $variantData['unit'] ?? null,
                        'price' => $variantData['price'] ?? null,
                        'stock' => $variantData['stock'] ?? null,
                    ]);
                }
            }
        }

        return redirect()->route('admin.products.index')->with('success', 'Product updated successfully');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('admin.products.index')->with('success', 'Product deleted successfully');
    }
}
