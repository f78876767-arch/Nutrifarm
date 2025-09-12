<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Discount extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'type',
        'value',
        'min_quantity',
        'get_quantity',
        'min_purchase_amount',
        'max_discount_amount',
        'usage_limit',
        'used_count',
        'is_active',
        'starts_at',
        'ends_at',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'min_purchase_amount' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'discount_product');
    }

    /**
     * Check if discount is currently active
     */
    public function isActive(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();
        if ($this->starts_at && $now->lt($this->starts_at)) {
            return false;
        }
        if ($this->ends_at && $now->gt($this->ends_at)) {
            return false;
        }
        if ($this->usage_limit && $this->used_count >= $this->usage_limit) {
            return false;
        }
        return true;
    }

    /**
     * Calculate discount amount for a given price and quantity
     */
    public function calculateDiscount(float $price, int $quantity = 1): float
    {
        if (!$this->isActive()) {
            return 0;
        }
        $discountAmount = 0;
        switch ($this->type) {
            case 'percentage':
                $discountAmount = ($price * $quantity) * ($this->value / 100);
                break;
            case 'fixed_amount':
                $discountAmount = $this->value;
                break;
            case 'buy_x_get_y':
                if ($quantity >= $this->min_quantity) {
                    $freeItems = intval($quantity / $this->min_quantity) * $this->get_quantity;
                    $discountAmount = min($freeItems, $quantity) * $price;
                }
                break;
        }
        if ($this->max_discount_amount && $discountAmount > $this->max_discount_amount) {
            $discountAmount = $this->max_discount_amount;
        }
        return round($discountAmount, 2);
    }

    /**
     * Get the status badge color for Filament
     */
    public function getStatusBadgeColor(): string
    {
        if (!$this->is_active) {
            return 'gray';
        }
        $now = now();
        if ($this->starts_at && $now->lt($this->starts_at)) {
            return 'warning';
        }
        if ($this->ends_at && $now->gt($this->ends_at)) {
            return 'danger';
        }
        return 'success';
    }

    /**
     * Get human-readable status
     */
    public function getStatusAttribute(): string
    {
        if (!$this->is_active) {
            return 'Inactive';
        }
        $now = now();
        if ($this->starts_at && $now->lt($this->starts_at)) {
            return 'Scheduled';
        }
        if ($this->ends_at && $now->gt($this->ends_at)) {
            return 'Expired';
        }
        if ($this->usage_limit && $this->used_count >= $this->usage_limit) {
            return 'Used Up';
        }
        return 'Active';
    }
}
