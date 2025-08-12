<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'user_id',
        'type',
        'quantity',
        'previous_quantity',
        'new_quantity',
        'reason'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'previous_quantity' => 'integer',
        'new_quantity' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getFormattedTypeAttribute()
    {
        return match ($this->type) {
            'add' => 'Stock Added',
            'subtract' => 'Stock Reduced',
            'set' => 'Stock Set',
            'bulk_update' => 'Bulk Update',
            'sale' => 'Sale',
            'return' => 'Return',
            'adjustment' => 'Manual Adjustment',
            default => ucfirst($this->type)
        };
    }
}
