<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with(['categories', 'variants'])->get();
        return response()->json([
            'success' => true,
            'data' => ProductResource::collection($products)
        ]);
    }

    public function segmented()
    {
        $products = Product::with('variants')->get();
        $discounted = [];
        $regular = [];
        
        foreach ($products as $p) {
            $primaryVariant = $p->primaryVariant();
            $data = [
                'id' => $p->id,
                'name' => $p->name,
                'price' => $primaryVariant ? (float)$primaryVariant->base_price : 0,
                'effective_price' => (float)$p->effective_price,
                'discount_amount' => $p->is_discount_active ? ($primaryVariant ? (float)$primaryVariant->discount_amount : null) : null,
                'is_discount_active' => (bool)$p->is_discount_active,
                'image_url' => $p->image_url,
                'total_sales' => (int)$p->total_sales,      // 🔥 ADD THIS
                'sales_count' => (int)$p->total_sales,      // 🔥 ADD THIS (alias)
                'variants' => $p->variants->map(function($v) {
                    return [
                        'id' => $v->id,
                        'name' => $v->name,
                        'value' => $v->value,
                        'base_price' => (float)$v->base_price,
                        'effective_price' => (float)$v->effective_price,
                        'discount_amount' => $v->isDiscountActive() ? (float)$v->discount_amount : null,
                        'is_discount_active' => $v->isDiscountActive(),
                        'stock_quantity' => (int)$v->stock_quantity,
                    ];
                }),
            ];
            
            if ($p->is_discount_active) {
                $discounted[] = $data;
            } else {
                $regular[] = $data;
            }
        }
        
        return response()->json([
            'discounted' => $discounted,
            'regular' => $regular,
        ]);
    }

    public function show($id)
    {
        $p = Product::with(['categories', 'variants'])->findOrFail($id);
        $primaryVariant = $p->primaryVariant();
        
        return [
            'id' => $p->id,
            'name' => $p->name,
            'description' => $p->description,
            'price' => $primaryVariant ? (float)$primaryVariant->base_price : 0,
            'effective_price' => (float)$p->effective_price,
            'discount_amount' => $p->is_discount_active ? ($primaryVariant ? (float)$primaryVariant->discount_amount : null) : null,
            'is_discount_active' => (bool)$p->is_discount_active,
            'image_url' => $p->image_url,
            'stock_quantity' => $primaryVariant ? (int)$primaryVariant->stock_quantity : 0,
            'categories' => $p->categories->pluck('name'),
            'variants' => $p->variants->map(function($v) {
                return [
                    'id' => $v->id,
                    'name' => $v->name,
                    'value' => $v->value,
                    'unit' => $v->unit,
                    'sku' => $v->sku,
                    'base_price' => (float)$v->base_price,
                    'effective_price' => (float)$v->effective_price,
                    'discount_amount' => $v->isDiscountActive() ? (float)$v->discount_amount : null,
                    'is_discount_active' => $v->isDiscountActive(),
                    'stock_quantity' => (int)$v->stock_quantity,
                    'weight' => $v->weight,
                    'is_active' => (bool)$v->is_active,
                ];
            }),
        ];
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image_path' => 'nullable|string',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'category_id' => 'nullable|exists:categories,id',
            'variants' => 'array',
            'variants.*.name' => 'required_with:variants|string|max:255',
            'variants.*.value' => 'required_with:variants|string|max:255',
            'variants.*.unit' => 'nullable|string|max:32',
            'variants.*.base_price' => 'required_with:variants|numeric|min:0',
            'variants.*.stock_quantity' => 'nullable|integer|min:0',
            'variants.*.discount_amount' => 'nullable|numeric|min:0',
            'variants.*.sku' => 'nullable|string|max:100',
            'variants.*.weight' => 'nullable|numeric|min:0',
            // Legacy support
            'price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'nullable|integer|min:0',
            'discount_amount' => 'nullable|numeric|min:0'
        ]);

        $productData = collect($validated)->only(['name', 'description', 'image_path', 'category_id'])->toArray();
        $productData['is_active'] = $request->get('is_active', true);
        $productData['is_featured'] = $request->get('is_featured', false);

        $product = Product::create($productData);

        // Create variants
        if (isset($validated['variants']) && !empty($validated['variants'])) {
            foreach ($validated['variants'] as $variantData) {
                $product->variants()->create([
                    'name' => $variantData['name'],
                    'value' => $variantData['value'],
                    'unit' => $variantData['unit'] ?? null,
                    'base_price' => $variantData['base_price'],
                    'stock_quantity' => $variantData['stock_quantity'] ?? 0,
                    'discount_amount' => $variantData['discount_amount'] ?? null,
                    'sku' => $variantData['sku'] ?? null,
                    'weight' => $variantData['weight'] ?? null,
                    'is_active' => true,
                ]);
            }
        } else {
            // Create default variant for legacy compatibility
            $product->variants()->create([
                'name' => 'Default',
                'value' => 'Standard',
                'base_price' => $validated['price'] ?? 0,
                'stock_quantity' => $validated['stock_quantity'] ?? 0,
                'discount_amount' => $validated['discount_amount'] ?? null,
                'is_active' => true,
            ]);
        }

        return response()->json($product->load('variants'), 201);
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $product->update($request->all());
        return response()->json($product);
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();
        return response()->json(null, 204);
    }

    /**
     * Get featured products
     */
    public function featured()
    {
        $products = Product::with(['categories', 'variants'])
            ->where('is_active', true)
            ->where('is_featured', true)
            ->get();

        return response()->json([
            'success' => true,
            'data' => ProductResource::collection($products)
        ]);
    }

    /**
     * Get popular products (sorted by sales count)
     */
    public function popular()
    {
        $products = Product::with(['categories', 'variants'])
            ->where('is_active', true)
            ->orderBy('total_sales', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => ProductResource::collection($products)
        ]);
    }
}
