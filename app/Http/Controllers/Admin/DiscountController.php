<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Discount;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DiscountController extends Controller
{
    public function index()
    {
        $discounts = Discount::withCount('products')
                           ->with('products')
                           ->latest()
                           ->paginate(15);

        // Calculate statistics
        $activeDiscountsCount = Discount::where('is_active', true)
                                      ->where(function($query) {
                                          $query->whereNull('starts_at')
                                                ->orWhere('starts_at', '<=', now());
                                      })
                                      ->where(function($query) {
                                          $query->whereNull('ends_at')
                                                ->orWhere('ends_at', '>=', now());
                                      })
                                      ->count();

        $totalUses = Discount::sum('used_count');
        
        // Calculate total savings (this would need order data integration)
        $totalSavings = 0; // Placeholder - you'd calculate this from actual order discounts

        return view('admin.discounts.index', compact(
            'discounts',
            'activeDiscountsCount',
            'totalUses',
            'totalSavings'
        ));
    }

    public function create()
    {
        $products = Product::active()->orderBy('name')->get();
        return view('admin.discounts.create', compact('products'));
    }

    public function store(Request $request)
    {
        $validationRules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:percentage,fixed_amount,buy_x_get_y',
            'min_purchase_amount' => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after:starts_at',
            'is_active' => 'boolean',
            'products' => 'array',
            'products.*' => 'exists:products,id',
        ];

        // Dynamic validation based on discount type
        switch ($request->type) {
            case 'percentage':
                $validationRules['value'] = 'required|numeric|min:0|max:100';
                break;
            case 'fixed_amount':
                $validationRules['value_fixed'] = 'required|numeric|min:0.01';
                break;
            case 'buy_x_get_y':
                $validationRules['value_buy'] = 'required|integer|min:1';
                $validationRules['get_quantity'] = 'required|integer|min:1';
                break;
        }

        $request->validate($validationRules);

        $discountData = $request->only([
            'name', 'description', 'type', 'min_purchase_amount', 
            'max_discount_amount', 'usage_limit', 'starts_at', 'ends_at', 'is_active'
        ]);

        // Set value based on discount type
        switch ($request->type) {
            case 'percentage':
                $discountData['value'] = $request->value;
                break;
            case 'fixed_amount':
                $discountData['value'] = $request->value_fixed;
                break;
            case 'buy_x_get_y':
                $discountData['value'] = $request->value_buy;
                $discountData['get_quantity'] = $request->get_quantity;
                break;
        }

        // Generate unique discount code if not provided
        if (empty($discountData['code'])) {
            $discountData['code'] = $this->generateDiscountCode($request->name);
        }

        DB::beginTransaction();
        try {
            $discount = Discount::create($discountData);

            if ($request->has('products') && !empty($request->products)) {
                $discount->products()->sync($request->products);
            }

            DB::commit();
            return redirect()->route('admin.discounts.index')
                           ->with('success', 'Discount created successfully');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Failed to create discount: ' . $e->getMessage()])
                        ->withInput();
        }
    }

    public function show(Discount $discount)
    {
        $discount->load('products');
        
        // Calculate statistics
        $usageStats = [
            'total_uses' => $discount->used_count ?? 0,
            'usage_limit' => $discount->usage_limit,
            'usage_percentage' => $discount->usage_limit > 0 
                ? round(($discount->used_count / $discount->usage_limit) * 100, 1) 
                : 0,
            'remaining_uses' => $discount->usage_limit 
                ? max(0, $discount->usage_limit - ($discount->used_count ?? 0))
                : null
        ];

        return view('admin.discounts.show', compact('discount', 'usageStats'));
    }

    public function edit(Discount $discount)
    {
        $products = Product::active()->orderBy('name')->get();
        $discount->load('products');
        return view('admin.discounts.edit', compact('discount', 'products'));
    }

    public function update(Request $request, Discount $discount)
    {
        $validationRules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:percentage,fixed_amount,buy_x_get_y',
            'min_purchase_amount' => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after:starts_at',
            'is_active' => 'boolean',
            'products' => 'array',
            'products.*' => 'exists:products,id',
        ];

        // Dynamic validation based on discount type
        switch ($request->type) {
            case 'percentage':
                $validationRules['value'] = 'required|numeric|min:0|max:100';
                break;
            case 'fixed_amount':
                $validationRules['value_fixed'] = 'required|numeric|min:0.01';
                break;
            case 'buy_x_get_y':
                $validationRules['value_buy'] = 'required|integer|min:1';
                $validationRules['get_quantity'] = 'required|integer|min:1';
                break;
        }

        $request->validate($validationRules);

        $discountData = $request->only([
            'name', 'description', 'type', 'min_purchase_amount', 
            'max_discount_amount', 'usage_limit', 'starts_at', 'ends_at', 'is_active'
        ]);

        // Set value based on discount type
        switch ($request->type) {
            case 'percentage':
                $discountData['value'] = $request->value;
                break;
            case 'fixed_amount':
                $discountData['value'] = $request->value_fixed;
                break;
            case 'buy_x_get_y':
                $discountData['value'] = $request->value_buy;
                $discountData['get_quantity'] = $request->get_quantity;
                break;
        }

        DB::beginTransaction();
        try {
            $discount->update($discountData);

            if ($request->has('products')) {
                $discount->products()->sync($request->products ?? []);
            }

            DB::commit();
            return redirect()->route('admin.discounts.index')
                           ->with('success', 'Discount updated successfully');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Failed to update discount: ' . $e->getMessage()])
                        ->withInput();
        }
    }

    public function destroy(Discount $discount)
    {
        try {
            $discount->delete();
            return redirect()->route('admin.discounts.index')
                           ->with('success', 'Discount deleted successfully');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to delete discount: ' . $e->getMessage()]);
        }
    }

    /**
     * Toggle discount active status
     */
    public function toggleStatus(Discount $discount)
    {
        $discount->update(['is_active' => !$discount->is_active]);
        
        $status = $discount->is_active ? 'activated' : 'deactivated';
        return response()->json([
            'success' => true,
            'message' => "Discount {$status} successfully",
            'is_active' => $discount->is_active
        ]);
    }

    /**
     * Bulk actions for discounts
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'discount_ids' => 'required|array',
            'discount_ids.*' => 'exists:discounts,id'
        ]);

        $discounts = Discount::whereIn('id', $request->discount_ids);
        
        switch ($request->action) {
            case 'activate':
                $discounts->update(['is_active' => true]);
                $message = 'Selected discounts activated successfully';
                break;
            case 'deactivate':
                $discounts->update(['is_active' => false]);
                $message = 'Selected discounts deactivated successfully';
                break;
            case 'delete':
                $discounts->delete();
                $message = 'Selected discounts deleted successfully';
                break;
        }

        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }

    /**
     * Generate a unique discount code
     */
    private function generateDiscountCode($name)
    {
        $baseCode = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $name), 0, 6));
        if (strlen($baseCode) < 3) {
            $baseCode = 'DISCOUNT';
        }
        
        $counter = 1;
        $code = $baseCode;
        
        while (Discount::where('code', $code)->exists()) {
            $code = $baseCode . $counter;
            $counter++;
        }
        
        return $code;
    }
}
