<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Category;
use App\Models\Product;
use App\Models\Discount;
use App\Models\FlashSale;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Create roles
        $adminRole = Role::firstOrCreate(['name' => 'Admin'], [
            'description' => 'Full access to all features'
        ]);
        
        $customerRole = Role::firstOrCreate(['name' => 'Customer'], [
            'description' => 'Regular customer access'
        ]);
        
        $managerRole = Role::firstOrCreate(['name' => 'Manager'], [
            'description' => 'Limited admin access'
        ]);

        // Create admin user
        $admin = User::firstOrCreate([
            'email' => 'admin@nutrifarm.com'
        ], [
            'name' => 'Admin User',
            'password' => Hash::make('password'),
            'address' => '123 Admin Street, Admin City',
            'email_verified_at' => now(),
        ]);
        
        $admin->roles()->sync([$adminRole->id]);

        // Create sample categories
        $categories = [
            ['name' => 'Vegetables'],
            ['name' => 'Fruits'],
            ['name' => 'Herbs'],
            ['name' => 'Organic'],
        ];

        foreach ($categories as $categoryData) {
            Category::firstOrCreate(['name' => $categoryData['name']], $categoryData);
        }

        // Create sample products
        $vegetableCategory = Category::where('name', 'Vegetables')->first();
        $fruitCategory = Category::where('name', 'Fruits')->first();
        $organicCategory = Category::where('name', 'Organic')->first();

        $products = [
            [
                'name' => 'Fresh Tomatoes',
                'description' => 'Juicy red tomatoes, perfect for salads and cooking',
                'price' => 4.99,
                'stock' => 50,
                'active' => true,
                'categories' => [$vegetableCategory->id, $organicCategory->id]
            ],
            [
                'name' => 'Organic Lettuce',
                'description' => 'Crisp organic lettuce leaves',
                'price' => 3.49,
                'stock' => 30,
                'active' => true,
                'categories' => [$vegetableCategory->id, $organicCategory->id]
            ],
            [
                'name' => 'Sweet Apples',
                'description' => 'Crisp and sweet red apples',
                'price' => 6.99,
                'discount_price' => 5.99,
                'stock' => 25,
                'active' => true,
                'categories' => [$fruitCategory->id]
            ],
            [
                'name' => 'Fresh Carrots',
                'description' => 'Orange carrots, rich in beta-carotene',
                'price' => 2.99,
                'stock' => 40,
                'active' => true,
                'categories' => [$vegetableCategory->id]
            ],
        ];

        foreach ($products as $productData) {
            $categories = $productData['categories'];
            unset($productData['categories']);
            
            $product = Product::firstOrCreate(['name' => $productData['name']], $productData);
            $product->categories()->sync($categories);
        }

        // Create sample discount
        $discount = Discount::firstOrCreate([
            'name' => 'Summer Sale 25%'
        ], [
            'description' => '25% off on selected vegetables',
            'type' => 'percentage',
            'value' => 25,
            'is_active' => true,
            'starts_at' => now(),
            'ends_at' => now()->addDays(30),
            'usage_limit' => 100,
            'used_count' => 0,
        ]);

        // Create sample flash sale
        $flashSale = FlashSale::firstOrCreate([
            'title' => '24-Hour Flash Sale'
        ], [
            'description' => 'Limited time offer - 40% off!',
            'discount_percentage' => 40,
            'max_discount_amount' => 50.00,
            'max_quantity' => 20,
            'sold_quantity' => 5,
            'is_active' => true,
            'starts_at' => now(),
            'ends_at' => now()->addHours(24),
        ]);

        $this->command->info('✅ Sample data created successfully!');
        $this->command->info('🔑 Admin login: admin@nutrifarm.com / password');
    }
}
