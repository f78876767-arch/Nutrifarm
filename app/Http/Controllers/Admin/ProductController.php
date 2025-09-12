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
    public function index(Request $request)
    {
        $q = trim((string) $request->get('q'));
        $active = $request->get('active');

        $products = Product::with('categories')
            ->when($q, function ($query) use ($q) {
                $query->where(function ($qq) use ($q) {
                    $qq->where('name', 'like', "%{$q}%")
                       ->orWhere('sku', 'like', "%{$q}%");
                });
            })
            ->when($active !== null && $active !== '', function ($query) use ($active) {
                $query->where('is_active', (bool) $active);
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.products.index', compact('products', 'q', 'active'));
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
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'image_path' => 'nullable|image|max:2048',
            'categories' => 'array',
            'variants.*.name' => 'required_with:variants.*.value|string|max:255',
            'variants.*.value' => 'required_with:variants.*.name|string|max:255',
            'variants.*.unit' => 'nullable|string|max:32',
            'variants.*.base_price' => 'nullable|numeric|min:0',
            'variants.*.stock_quantity' => 'nullable|integer|min:0',
            'variants.*.discount_amount' => 'nullable|numeric|min:0',
            'variants.*.sku' => 'nullable|string|max:100|unique:variants,sku',
            'variants.*.weight' => 'nullable|numeric|min:0',
            // Legacy fields for backward compatibility
            'price' => 'nullable|numeric|min:0',
            'sku' => 'nullable|string|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
        ]);

        $productData = $request->only(['name', 'description']);
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

        // Create variants
        if ($request->has('variants')) {
            foreach ($request->variants as $variantData) {
                if (!empty($variantData['name']) && !empty($variantData['value'])) {
                    $product->variants()->create([
                        'name' => $variantData['name'],
                        'value' => $variantData['value'],
                        'unit' => $variantData['unit'] ?? null,
                        'base_price' => isset($variantData['base_price']) ? preg_replace('/[^\d.]/', '', $variantData['base_price']) : null,
                        'stock_quantity' => $variantData['stock_quantity'] ?? 0,
                        'discount_amount' => isset($variantData['discount_amount']) ? preg_replace('/[^\d.]/', '', $variantData['discount_amount']) : null,
                        'sku' => $variantData['sku'] ?? null,
                        'weight' => $variantData['weight'] ?? null,
                        'is_active' => true,
                    ]);
                }
            }
        } else {
            // Ensure at least one default variant exists
            $product->variants()->create([
                'name' => 'Default',
                'value' => 'Standard',
                'base_price' => isset($request->price) ? preg_replace('/[^\d.]/', '', $request->price) : 0,
                'stock_quantity' => 0,
                'discount_amount' => isset($request->discount_amount) ? preg_replace('/[^\d.]/', '', $request->discount_amount) : null,
                'sku' => $request->sku,
                'is_active' => true,
            ]);
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
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'image_path' => 'nullable|image|max:2048',
            'categories' => 'array',
            'variants.*.name' => 'required_with:variants.*.value|string|max:255',
            'variants.*.value' => 'required_with:variants.*.name|string|max:255',
            'variants.*.unit' => 'nullable|string|max:32',
            'variants.*.base_price' => 'nullable|numeric|min:0',
            'variants.*.stock_quantity' => 'nullable|integer|min:0',
            'variants.*.discount_amount' => 'nullable|numeric|min:0',
            'variants.*.sku' => 'nullable|string|max:100',
            'variants.*.weight' => 'nullable|numeric|min:0',
            // Legacy fields for backward compatibility
            'price' => 'nullable|numeric|min:0',
            'sku' => 'nullable|string|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
        ]);

        $productData = $request->only(['name', 'description']);
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

        // Replace variants with submitted list
        $product->variants()->delete();
        if ($request->has('variants')) {
            foreach ($request->variants as $variantData) {
                if (!empty($variantData['name']) && !empty($variantData['value'])) {
                    $product->variants()->create([
                        'name' => $variantData['name'],
                        'value' => $variantData['value'],
                        'unit' => $variantData['unit'] ?? null,
                        'base_price' => isset($variantData['base_price']) ? preg_replace('/[^\d.]/', '', $variantData['base_price']) : null,
                        'stock_quantity' => $variantData['stock_quantity'] ?? 0,
                        'discount_amount' => isset($variantData['discount_amount']) ? preg_replace('/[^\d.]/', '', $variantData['discount_amount']) : null,
                        'sku' => $variantData['sku'] ?? null,
                        'weight' => $variantData['weight'] ?? null,
                        'is_active' => true,
                    ]);
                }
            }
        } else {
            // Ensure at least one default variant exists
            $product->variants()->create([
                'name' => 'Default',
                'value' => 'Standard',
                'base_price' => isset($request->price) ? preg_replace('/[^\d.]/', '', $request->price) : 0,
                'stock_quantity' => 0,
                'discount_amount' => isset($request->discount_amount) ? preg_replace('/[^\d.]/', '', $request->discount_amount) : null,
                'sku' => $request->sku,
                'is_active' => true,
            ]);
        }

        return redirect()->route('admin.products.index')->with('success', 'Product updated successfully');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('admin.products.index')->with('success', 'Product deleted successfully');
    }

    public function toggleDiscount(Product $product)
    {
        $primaryVariant = $product->primaryVariant();
        
        if (!$primaryVariant) {
            return response()->json(['success' => false, 'message' => 'Produk tidak memiliki varian'], 400);
        }
        
        if ($primaryVariant->isDiscountActive()) {
            $primaryVariant->update(['discount_amount' => null]);
            return response()->json(['success' => true, 'message' => 'Diskon dimatikan']);
        }
        return response()->json(['success' => false, 'message' => 'Tidak ada diskon aktif untuk dimatikan'], 400);
    }
}
