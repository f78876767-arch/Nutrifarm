<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'name' => 'Cuka Apel',
                'description' => 'Cuka apel organik untuk kesehatan.',
                // legacy price kept for compatibility, UI should use variants
                'price' => 35000,
                'stock_quantity' => 100,
                'image_path' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Virgin Coconut Oil',
                'description' => 'Minyak kelapa murni multifungsi.',
                'price' => 50000,
                'stock_quantity' => 80,
                'image_path' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Madu Hutan',
                'description' => 'Madu asli dari hutan tropis.',
                'price' => 60000,
                'stock_quantity' => 60,
                'image_path' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Teh Herbal',
                'description' => 'Teh herbal alami untuk relaksasi.',
                'price' => 25000,
                'stock_quantity' => 120,
                'image_path' => null,
                'is_active' => true,
            ],
        ];
        foreach ($products as $data) {
            Product::updateOrCreate(['name' => $data['name']], $data);
        }
    }
}
