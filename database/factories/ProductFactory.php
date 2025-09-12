<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            // Keep legacy price for compatibility; app primarily uses variants
            'price' => $this->faker->numberBetween(10000, 1000000),
            'stock_quantity' => $this->faker->numberBetween(10, 100),
            'image_path' => null,
            'is_active' => true,
        ];
    }
}
