<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Variant;
use App\Models\Product;
use Illuminate\Http\Request;

class VariantController extends Controller
{
    public function index()
    {
        $variants = Variant::with('product')
            ->orderBy('product_id')
            ->orderBy('name')
            ->paginate(15);
        
        return view('admin.variants.index', compact('variants'));
    }

    public function create()
    {
        $products = Product::orderBy('name')->get();
        $units = ['kg', 'g', 'l', 'ml', 'pcs', 'pack'];
        
        return view('admin.variants.create', compact('products', 'units'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'name' => 'required|string|max:255',
            'value' => 'required|string|max:255',
            'unit' => 'nullable|string|max:32',
            'custom_unit' => 'nullable|string|max:64',
            'price' => 'nullable|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
        ]);

        $variantData = $request->all();
        
        // Convert formatted price to number
        if ($request->price) {
            $variantData['price'] = (float) str_replace([',', '.'], ['', ''], $request->price);
        }

        Variant::create($variantData);

        return redirect()->route('admin.variants.index')->with('success', 'Variant created successfully');
    }

    public function show(Variant $variant)
    {
        $variant->load('product');
        return view('admin.variants.show', compact('variant'));
    }

    public function edit(Variant $variant)
    {
        $products = Product::orderBy('name')->get();
        $units = ['kg', 'g', 'l', 'ml', 'pcs', 'pack'];
        
        return view('admin.variants.edit', compact('variant', 'products', 'units'));
    }

    public function update(Request $request, Variant $variant)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'name' => 'required|string|max:255',
            'value' => 'required|string|max:255',
            'unit' => 'nullable|string|max:32',
            'custom_unit' => 'nullable|string|max:64',
            'price' => 'nullable|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
        ]);

        $variantData = $request->all();
        
        // Convert formatted price to number
        if ($request->price) {
            $variantData['price'] = (float) str_replace([',', '.'], ['', ''], $request->price);
        }

        $variant->update($variantData);

        return redirect()->route('admin.variants.index')->with('success', 'Variant updated successfully');
    }

    public function destroy(Variant $variant)
    {
        $variant->delete();
        return redirect()->route('admin.variants.index')->with('success', 'Variant deleted successfully');
    }
}
