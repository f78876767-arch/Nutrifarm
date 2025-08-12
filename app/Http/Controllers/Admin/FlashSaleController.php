<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FlashSale;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FlashSaleController extends Controller
{
    public function index()
    {
        $flashSales = FlashSale::with('products')
                              ->latest()
                              ->paginate(15);

        // Calculate statistics
        $activeFlashSalesCount = FlashSale::where('is_active', true)
                                        ->where('starts_at', '<=', now())
                                        ->where('ends_at', '>=', now())
                                        ->whereRaw('(sold_quantity < max_quantity OR max_quantity IS NULL)')
                                        ->count();

        $totalItemsSold = FlashSale::sum('sold_quantity');
        
        $totalRevenue = FlashSale::selectRaw('SUM(sold_quantity * discount_percentage / 100 * (SELECT AVG(price) FROM products INNER JOIN flash_sale_product ON products.id = flash_sale_product.product_id WHERE flash_sale_product.flash_sale_id = flash_sales.id)) as revenue')
                                ->value('revenue') ?? 0;

        return view('admin.flash-sales.index', compact(
            'flashSales',
            'activeFlashSalesCount', 
            'totalItemsSold',
            'totalRevenue'
        ));
    }

    public function create()
    {
        $products = Product::active()
                          ->whereDoesntHave('flashSales', function ($query) {
                              $query->where('is_active', true)
                                    ->where('ends_at', '>=', now());
                          })
                          ->orderBy('name')
                          ->get();
        return view('admin.flash-sales.create', compact('products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'discount_percentage' => 'required|numeric|min:0.01|max:99.99',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'max_quantity' => 'nullable|integer|min:1',
            'starts_at' => 'required|date|after_or_equal:now',
            'ends_at' => 'required|date|after:starts_at',
            'is_active' => 'boolean',
            'products' => 'required|array|min:1',
            'products.*' => 'exists:products,id',
        ]);

        DB::beginTransaction();
        try {
            $flashSaleData = $request->only([
                'title', 'description', 'discount_percentage', 
                'max_discount_amount', 'max_quantity', 
                'starts_at', 'ends_at', 'is_active'
            ]);
            $flashSaleData['sold_quantity'] = 0;
            $flashSaleData['is_active'] = $request->has('is_active');
            
            $flashSale = FlashSale::create($flashSaleData);
            
            // Attach selected products
            if ($request->has('products')) {
                $flashSale->products()->sync($request->products);
            }

            DB::commit();
            return redirect()->route('admin.flash-sales.index')
                           ->with('success', 'Flash sale created successfully');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Failed to create flash sale: ' . $e->getMessage()])
                        ->withInput();
        }
    }

    public function show(FlashSale $flashSale)
    {
        $flashSale->load('products');
        
        // Calculate performance metrics
        $performance = [
            'sold_percentage' => $flashSale->max_quantity > 0 
                ? round(($flashSale->sold_quantity / $flashSale->max_quantity) * 100, 1) 
                : 0,
            'revenue_generated' => 0, // Calculate based on products and discount
            'items_remaining' => $flashSale->max_quantity ? max(0, $flashSale->max_quantity - $flashSale->sold_quantity) : null,
            'time_remaining' => $flashSale->ends_at > now() 
                ? $flashSale->ends_at->diffForHumans(now(), true) 
                : null,
            'is_active' => $flashSale->isActive(),
            'products_count' => $flashSale->products->count(),
        ];

        // Calculate total potential revenue from all products
        if ($flashSale->products->isNotEmpty()) {
            $totalRevenue = 0;
            foreach ($flashSale->products as $product) {
                $discountAmount = $flashSale->calculateDiscount($product->price);
                $salePrice = $product->price - $discountAmount;
                $totalRevenue += $salePrice * ($product->pivot->sale_quantity ?? 0);
            }
            $performance['revenue_generated'] = $totalRevenue;
        }

        return view('admin.flash-sales.show', compact('flashSale', 'performance'));
    }

    public function edit(FlashSale $flashSale)
    {
        $products = Product::active()->orderBy('name')->get();
        $flashSale->load('products');
        return view('admin.flash-sales.edit', compact('flashSale', 'products'));
    }

    public function update(Request $request, FlashSale $flashSale)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'discount_percentage' => 'required|numeric|min:0.01|max:99.99',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'max_quantity' => 'nullable|integer|min:' . $flashSale->sold_quantity,
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after:starts_at',
            'is_active' => 'boolean',
            'products' => 'required|array|min:1',
            'products.*' => 'exists:products,id',
        ]);

        DB::beginTransaction();
        try {
            $flashSaleData = $request->only([
                'title', 'description', 'discount_percentage', 
                'max_discount_amount', 'max_quantity', 
                'starts_at', 'ends_at', 'is_active'
            ]);
            $flashSaleData['is_active'] = $request->has('is_active');
            
            $flashSale->update($flashSaleData);
            
            // Update product relationships
            if ($request->has('products')) {
                $flashSale->products()->sync($request->products);
            }

            DB::commit();
            return redirect()->route('admin.flash-sales.index')
                           ->with('success', 'Flash sale updated successfully');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Failed to update flash sale: ' . $e->getMessage()])
                        ->withInput();
        }
    }

    public function destroy(FlashSale $flashSale)
    {
        try {
            // Check if flash sale has sales
            if ($flashSale->sold_quantity > 0) {
                return back()->withErrors(['error' => 'Cannot delete flash sale that has already made sales.']);
            }

            $flashSale->delete();
            return redirect()->route('admin.flash-sales.index')
                           ->with('success', 'Flash sale deleted successfully');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to delete flash sale: ' . $e->getMessage()]);
        }
    }

    /**
     * Toggle flash sale active status
     */
    public function toggleStatus(FlashSale $flashSale)
    {
        $flashSale->update(['is_active' => !$flashSale->is_active]);
        
        $status = $flashSale->is_active ? 'activated' : 'deactivated';
        return response()->json([
            'success' => true,
            'message' => "Flash sale {$status} successfully",
            'is_active' => $flashSale->is_active
        ]);
    }

    /**
     * Bulk actions for flash sales
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'flash_sale_ids' => 'required|array',
            'flash_sale_ids.*' => 'exists:flash_sales,id'
        ]);

        $flashSales = FlashSale::whereIn('id', $request->flash_sale_ids);
        
        switch ($request->action) {
            case 'activate':
                $flashSales->update(['is_active' => true]);
                $message = 'Selected flash sales activated successfully';
                break;
            case 'deactivate':
                $flashSales->update(['is_active' => false]);
                $message = 'Selected flash sales deactivated successfully';
                break;
            case 'delete':
                // Check if any have sales
                $withSales = $flashSales->where('sold_quantity', '>', 0)->exists();
                if ($withSales) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot delete flash sales that have already made sales'
                    ], 400);
                }
                $flashSales->delete();
                $message = 'Selected flash sales deleted successfully';
                break;
        }

        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }

    /**
     * Get flash sale analytics
     */
    public function analytics(FlashSale $flashSale)
    {
        $analytics = [
            'hourly_sales' => [], // Would implement hourly sales tracking
            'conversion_rate' => 0, // Would calculate from views vs purchases
            'revenue_impact' => 0,
            'quantity_left' => $flashSale->max_quantity ? max(0, $flashSale->max_quantity - $flashSale->sold_quantity) : null,
        ];

        // Calculate revenue impact from all products
        $totalRevenue = 0;
        foreach ($flashSale->products as $product) {
            $discountAmount = $flashSale->calculateDiscount($product->price);
            $salePrice = $product->price - $discountAmount;
            $totalRevenue += $salePrice * ($product->pivot->sale_quantity ?? 0);
        }
        $analytics['revenue_impact'] = $totalRevenue;

        return response()->json($analytics);
    }
}
