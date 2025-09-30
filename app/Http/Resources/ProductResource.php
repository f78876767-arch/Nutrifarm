<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $primaryVariant = $this->primaryVariant();
        
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $primaryVariant ? (float)$primaryVariant->base_price : 0,
            'effective_price' => (float)$this->effective_price,
            'discount_amount' => $this->is_discount_active ? ($primaryVariant ? (float)$primaryVariant->discount_amount : null) : null,
            'is_discount_active' => (bool)$this->is_discount_active,
            'image_url' => $this->image_url,
            'stock_quantity' => $primaryVariant ? (int)$primaryVariant->stock_quantity : 0,
            'is_active' => $this->is_active,
            'is_featured' => $this->is_featured,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'categories' => $this->whenLoaded('categories', function() {
                return $this->categories->pluck('name');
            }),
            'variants' => $this->whenLoaded('variants', function() {
                return $this->variants->map(function($v) {
                    return [
                        'id' => $v->id,
                        'name' => $v->name,
                        'value' => $v->value,
                        'unit' => $v->unit,
                        'sku' => $v->sku,
                        'base_price' => (float)$v->base_price,
                        'effective_price' => (float)$v->effective_price,
                        'discount_amount' => $v->isDiscountActive() ? (float)$v->discount_amount : null,
                        'is_discount_active' => $v->isDiscountActive(),
                        'stock_quantity' => (int)$v->stock_quantity,
                        'weight' => $v->weight,
                        'is_active' => (bool)$v->is_active,
                    ];
                });
            }),
            // Sales data
            'total_sales' => $this->total_sales ?? 0,
            'sales_count' => $this->total_sales ?? 0,
        ];
    }
}
