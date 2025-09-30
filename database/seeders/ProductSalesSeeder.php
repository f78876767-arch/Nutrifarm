<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSalesSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Add realistic sales data to products
        Product::all()->each(function ($product) {
            $salesCount = rand(0, 500); // Random sales between 0-500
            
            $product->update([
                'total_sales' => $salesCount,
                'sales_count' => $salesCount
            ]);
            
            $this->command->info("Updated product '{$product->name}' with {$salesCount} sales");
        });
        
        $this->command->info('Product sales data seeded successfully!');
    }
}
