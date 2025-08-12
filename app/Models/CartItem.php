<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id',
        'product_id',
        'variant_id',
        'quantity'
    ];
    
    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    
    public function variant()
    {
        return $this->belongsTo(Variant::class);
    }
    
    // Get the effective price (variant price or product price)
    public function getPrice()
    {
        if ($this->variant && $this->variant->price) {
            return $this->variant->price;
        }
        return $this->product->discount_price ?? $this->product->price;
    }
    
    // Get total price for this cart item (price * quantity)
    public function getTotalPrice()
    {
        return $this->getPrice() * $this->quantity;
    }
}
