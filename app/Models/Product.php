<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'description', 'price', 'stock_quantity', 'image_path', 'is_active', 'is_featured', 'sku', 'discount_price', 'category_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'price' => 'decimal:2',
        'discount_price' => 'decimal:2',
    ];

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

    /**
     * Calculate final price after all discounts
     */
    public function getFinalPrice(int $quantity = 1): float
    {
        $basePrice = $this->price;
        $totalDiscount = 0;

        // Apply regular discounts
        $activeDiscounts = $this->getActiveDiscounts();
        foreach ($activeDiscounts as $discount) {
            $totalDiscount += $discount->calculateDiscount($basePrice, $quantity);
        }

        // Apply flash sale discounts (usually higher priority)
        $activeFlashSales = $this->getActiveFlashSales();
        foreach ($activeFlashSales as $flashSale) {
            $flashDiscount = $flashSale->calculateDiscount($basePrice);
            if ($flashDiscount > $totalDiscount) {
                $totalDiscount = $flashDiscount; // Use highest discount
            }
        }

        return max(0, $basePrice - $totalDiscount);
    }

    /**
     * Check if product has any active promotions
     */
    public function hasActivePromotions(): bool
    {
        return $this->getActiveDiscounts()->isNotEmpty() || 
               $this->getActiveFlashSales()->isNotEmpty();
    }
}
