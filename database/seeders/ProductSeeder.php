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
                'price' => 35000,
                'stock' => 100,
                'image' => null,
            ],
            [
                'name' => 'Virgin Coconut Oil',
                'description' => 'Minyak kelapa murni multifungsi.',
                'price' => 50000,
                'stock' => 80,
                'image' => null,
            ],
            [
                'name' => 'Madu Hutan',
                'description' => 'Madu asli dari hutan tropis.',
                'price' => 60000,
                'stock' => 60,
                'image' => null,
            ],
            [
                'name' => 'Teh Herbal',
                'description' => 'Teh herbal alami untuk relaksasi.',
                'price' => 25000,
                'stock' => 120,
                'image' => null,
            ],
        ];
        foreach ($products as $data) {
            Product::create($data);
        }
    }
}
