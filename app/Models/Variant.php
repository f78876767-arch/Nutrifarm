<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Variant extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'product_id', 'name', 'value', 'unit', 'custom_unit', 'sku',
        'base_price', 'discount_amount', 'stock_quantity', 'is_active', 'weight',
        // legacy support
        'price', 'stock'
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'stock_quantity' => 'integer',
        'is_active' => 'boolean',
        'weight' => 'decimal:2',
        // legacy
        'price' => 'decimal:2',
        'stock' => 'integer',
    ];

    protected $appends = ['effective_price', 'is_discount_active'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Discount logic (same as Product model)
    public function isDiscountActive(): bool
    {
        return !is_null($this->discount_amount) && 
               $this->discount_amount > 0 && 
               $this->discount_amount < $this->base_price;
    }

    public function getIsDiscountActiveAttribute(): bool
    {
        return $this->isDiscountActive();
    }

    public function getEffectivePriceAttribute(): float
    {
        if ($this->isDiscountActive()) {
            return (float) max(0, $this->base_price - $this->discount_amount);
        }
        return (float) $this->base_price;
    }

    // Legacy compatibility - map old price field to base_price
    public function getPriceAttribute($value)
    {
        return $this->base_price ?? $value;
    }

    public function getStockAttribute($value)
    {
        return $this->stock_quantity ?? $value;
    }

    // Scope for active variants
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Check stock availability
    public function hasStock(int $quantity = 1): bool
    {
        return $this->stock_quantity >= $quantity;
    }
}
