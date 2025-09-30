<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'description', 'image_path', 'is_active', 'is_featured', 'category_id',
        'total_sales', 'sales_count',
        // Legacy fields for backward compatibility (will be deprecated)
        'sku', 'price', 'stock_quantity', 'discount_price', 'discount_amount'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        // Legacy casts
        'price' => 'decimal:2',
        'discount_price' => 'decimal:2', 
        'discount_amount' => 'decimal:2',
    ];

    protected $appends = ['effective_price','is_discount_active', 'total_stock', 'rating_avg', 'rating_count'];

    /**
     * Scope for active products
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Relationship with active flash sales
     */
    public function activeFlashSales()
    {
        return $this->hasMany(FlashSale::class)->where('is_active', true)
                    ->where('starts_at', '<=', now())
                    ->where('ends_at', '>=', now());
    }

    /**
     * Get image URL with fallback
     */
    public function getImageUrlAttribute()
    {
        if ($this->image_path) {
            return asset('storage/' . $this->image_path);
        }
        return 'https://via.placeholder.com/300x300?text=' . urlencode($this->name);
    }

    public function orderProducts()
    {
        return $this->hasMany(OrderProduct::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function variants()
    {
        return $this->hasMany(Variant::class);
    }

    public function discounts()
    {
        return $this->belongsToMany(Discount::class, 'discount_product');
    }

    public function flashSales()
    {
        return $this->belongsToMany(FlashSale::class, 'flash_sale_product')
            ->withPivot('sale_quantity')
            ->withTimestamps();
    }

    /**
     * Get active discounts for this product
     */
    public function getActiveDiscounts()
    {
        return $this->discounts()
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            })
            ->get();
    }

    /**
     * Get active flash sales for this product
     */
    public function getActiveFlashSales()
    {
        return $this->flashSales()
            ->where('is_active', true)
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now())
            ->get();
    }

    // Get primary variant (first active variant or fallback)
    public function primaryVariant()
    {
        return $this->variants()->active()->first() ?? $this->variants()->first();
    }

    // Get cheapest variant price
    public function getMinPriceAttribute()
    {
        $variant = $this->variants()->orderBy('base_price')->first();
        return $variant ? $variant->effective_price : 0;
    }

    // Get most expensive variant price  
    public function getMaxPriceAttribute()
    {
        $variant = $this->variants()->orderByDesc('base_price')->first();
        return $variant ? $variant->effective_price : 0;
    }

    // Check if any variant has discount
    public function hasActivePromotions(): bool
    {
        return $this->variants()->whereNotNull('discount_amount')
                   ->where('discount_amount', '>', 0)->exists();
    }

    // Legacy compatibility - delegate to primary variant
    public function isDiscountActive(): bool
    {
        $primary = $this->primaryVariant();
        return $primary ? $primary->isDiscountActive() : false;
    }

    public function getIsDiscountActiveAttribute(): bool
    {
        return $this->isDiscountActive();
    }

    public function getEffectivePriceAttribute(): float
    {
        $primary = $this->primaryVariant();
        return $primary ? $primary->effective_price : 0;
    }

    // Legacy price - from primary variant or fallback to product price
    public function getPriceAttribute($value)
    {
        $primary = $this->primaryVariant();
        return $primary ? $primary->base_price : ($value ?? 0);
    }

    /**
     * Derived stock: sum of all variant stock quantities.
     */
    public function getStockQuantityAttribute($value): int
    {
        if ($this->relationLoaded('variants')) {
            return (int) $this->variants->sum(function ($v) { return (int) ($v->stock_quantity ?? 0); });
        }
        return (int) ($this->variants()->sum('stock_quantity'));
    }

    /**
     * Alias for clarity in views/APIs.
     */
    public function getTotalStockAttribute(): int
    {
        return (int) $this->stock_quantity;
    }

    /**
     * Calculate final price after all discounts
     */
    public function getFinalPrice(int $quantity = 1): float
    {
        return $this->effective_price;
    }

    public function reviews()
    {
        return $this->hasMany(Review::class)->where('is_approved', true);
    }

    public function getRatingAvgAttribute(): float
    {
        $avg = $this->reviews()->avg('rating');
        return $avg ? round((float) $avg, 2) : 0.0;
    }

    public function getRatingCountAttribute(): int
    {
        return (int) $this->reviews()->count();
    }

    /**
     * Relationship with order items
     */
    public function orderItems()
    {
        return $this->hasMany(\App\Models\OrderItem::class);
    }

    /**
     * Get total sales from completed orders
     */
    public function getTotalSalesAttribute()
    {
        if (isset($this->attributes['total_sales'])) {
            return $this->attributes['total_sales'];
        }
        
        return $this->calculateTotalSales();
    }

    /**
     * Calculate total sales from order items
     */
    public function calculateTotalSales()
    {
        if (class_exists('\App\Models\OrderItem')) {
            $totalSales = $this->orderItems()
                ->whereHas('order', function($query) {
                    $query->where('status', 'completed');
                })
                ->sum('quantity');
            
            // Update the cached value
            $this->update(['total_sales' => $totalSales]);
            return $totalSales;
        }
        
        return $this->attributes['total_sales'] ?? 0;
    }

    /**
     * Increment sales count when order is completed
     */
    public function incrementSales(int $quantity = 1)
    {
        $this->increment('total_sales', $quantity);
        $this->increment('sales_count', $quantity);
    }
}
