<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Discount;
use App\Models\Product;
use App\Models\FlashSale;

class DiscountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create some sample discounts
        $discounts = [
            [
                'name' => 'Summer Sale 2025',
                'description' => 'Get 20% off on all summer products',
                'type' => 'percentage',
                'value' => 20,
                'is_active' => true,
                'starts_at' => now()->subDays(5),
                'ends_at' => now()->addDays(30),
            ],
            [
                'name' => 'New Customer Special',
                'description' => 'Get $10 off your first order',
                'type' => 'fixed_amount',
                'value' => 10,
                'min_purchase_amount' => 50,
                'usage_limit' => 100,
                'is_active' => true,
            ],
            [
                'name' => 'Buy 2 Get 1 Free',
                'description' => 'Buy any 2 items and get 1 free',
                'type' => 'buy_x_get_y',
                'value' => 0,
                'min_quantity' => 2,
                'get_quantity' => 1,
                'is_active' => true,
                'starts_at' => now(),
                'ends_at' => now()->addDays(15),
            ],
            [
                'name' => 'VIP Members Discount',
                'description' => '30% off for VIP members (scheduled)',
                'type' => 'percentage',
                'value' => 30,
                'max_discount_amount' => 50,
                'is_active' => true,
                'starts_at' => now()->addDays(7),
                'ends_at' => now()->addDays(14),
            ],
            [
                'name' => 'Expired Sale',
                'description' => 'This sale has ended',
                'type' => 'percentage',
                'value' => 15,
                'is_active' => true,
                'starts_at' => now()->subDays(20),
                'ends_at' => now()->subDays(5),
            ],
        ];

        foreach ($discounts as $discountData) {
            $discount = Discount::create($discountData);
            
            // Attach some random products to each discount
            $products = Product::inRandomOrder()->limit(rand(2, 5))->get();
            if ($products->isNotEmpty()) {
                $discount->products()->attach($products->pluck('id'));
            }
        }

        // Create some flash sales
        $flashSales = [
            [
                'title' => '⚡ Flash Sale: 48 Hours Only!',
                'description' => 'Limited time offer - 40% off selected items',
                'discount_percentage' => 40,
                'max_quantity' => 50,
                'sold_quantity' => 23,
                'is_active' => true,
                'starts_at' => now()->subHours(6),
                'ends_at' => now()->addHours(42),
            ],
            [
                'title' => '🔥 Weekend Flash Sale',
                'description' => 'Weekend special - 25% off everything',
                'discount_percentage' => 25,
                'max_discount_amount' => 30,
                'is_active' => true,
                'starts_at' => now()->addDays(2),
                'ends_at' => now()->addDays(4),
            ],
            [
                'title' => 'Midnight Flash Sale',
                'description' => 'One hour only - 50% off',
                'discount_percentage' => 50,
                'max_quantity' => 20,
                'sold_quantity' => 20, // Sold out
                'is_active' => true,
                'starts_at' => now()->subDays(1),
                'ends_at' => now()->subHours(22),
            ],
        ];

        foreach ($flashSales as $flashSaleData) {
            $flashSale = FlashSale::create($flashSaleData);
            
            // Attach some random products to each flash sale
            $products = Product::inRandomOrder()->limit(rand(3, 8))->get();
            if ($products->isNotEmpty()) {
                $flashSale->products()->attach($products->pluck('id'));
            }
        }
    }
}
