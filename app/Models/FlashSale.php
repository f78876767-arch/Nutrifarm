<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;

class FlashSale extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'discount_percentage',
        'max_discount_amount',
        'max_quantity',
        'sold_quantity',
        'is_active',
        'starts_at',
        'ends_at',
    ];

    protected $casts = [
        'discount_percentage' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'flash_sale_product')
            ->withPivot('sale_quantity')
            ->withTimestamps();
    }

    /**
     * Check if flash sale is currently active
     */
    public function isActive(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();
        
        if ($now->lt($this->starts_at) || $now->gt($this->ends_at)) {
            return false;
        }

        if ($this->max_quantity && $this->sold_quantity >= $this->max_quantity) {
            return false;
        }

        return true;
    }

    /**
     * Calculate flash sale discount for a given price
     */
    public function calculateDiscount(float $price): float
    {
        if (!$this->isActive()) {
            return 0;
        }

        $discountAmount = $price * ($this->discount_percentage / 100);

        // Apply maximum discount limit
        if ($this->max_discount_amount && $discountAmount > $this->max_discount_amount) {
            $discountAmount = $this->max_discount_amount;
        }

        return round($discountAmount, 2);
    }

    /**
     * Get remaining quantity for sale
     */
    public function getRemainingQuantity(): ?int
    {
        if (!$this->max_quantity) {
            return null; // Unlimited
        }

        return max(0, $this->max_quantity - $this->sold_quantity);
    }

    /**
     * Get progress percentage
     */
    public function getProgressPercentage(): float
    {
        if (!$this->max_quantity) {
            return 0;
        }

        return round(($this->sold_quantity / $this->max_quantity) * 100, 1);
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
        
        if ($now->lt($this->starts_at)) {
            return 'warning'; // Upcoming
        }

        if ($now->gt($this->ends_at)) {
            return 'danger'; // Expired
        }

        if ($this->max_quantity && $this->sold_quantity >= $this->max_quantity) {
            return 'danger'; // Sold out
        }

        return 'success'; // Active
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
        
        if ($now->lt($this->starts_at)) {
            return 'Upcoming';
        }

        if ($now->gt($this->ends_at)) {
            return 'Expired';
        }

        if ($this->max_quantity && $this->sold_quantity >= $this->max_quantity) {
            return 'Sold Out';
        }

        return 'Active';
    }

    /**
     * Record sale for a specific product and update counts safely
     */
    public function recordSale(int $productId, int $quantity): int
    {
        if ($quantity <= 0) {
            return 0;
        }

        // Lock row for update to avoid race conditions
        return DB::transaction(function () use ($productId, $quantity) {
            $fresh = $this->newQuery()->lockForUpdate()->find($this->id);

            if (!$fresh || !$fresh->isActive()) {
                return 0;
            }

            $remainingCap = $fresh->max_quantity ? max(0, $fresh->max_quantity - $fresh->sold_quantity) : $quantity;

            if ($remainingCap <= 0) {
                return 0;
            }

            $applyQty = $fresh->max_quantity ? min($quantity, $remainingCap) : $quantity;

            // Update pivot
            $pivot = $fresh->products()->where('product_id', $productId)->first();

            if ($pivot) {
                $currentPivot = $pivot->pivot->sale_quantity ?? 0;
                $pivot->pivot->sale_quantity = $currentPivot + $applyQty;
                $pivot->pivot->save();
            }

            $fresh->increment('sold_quantity', $applyQty);

            return $applyQty;
        });
    }
}
