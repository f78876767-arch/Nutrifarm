<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Generate 5 random users (besides admin/staff)
        $randomUsers = \App\Models\User::factory()->count(5)->create();

        $superAdmin = \App\Models\User::updateOrCreate(
            ['email' => 'superadmin@example.com'],
            [
                'name' => 'Super Admin',
                'password' => bcrypt('password'),
            ]
        );
        $admin = \App\Models\User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('password'),
            ]
        );
        $staff = \App\Models\User::updateOrCreate(
            ['email' => 'staff@example.com'],
            [
                'name' => 'Staff User',
                'password' => bcrypt('password'),
            ]
        );

        $superAdminRole = \App\Models\Role::updateOrCreate(['name' => 'super_admin']);
        $adminRole = \App\Models\Role::updateOrCreate(['name' => 'admin']);
        $staffRole = \App\Models\Role::updateOrCreate(['name' => 'staff']);

        $superAdmin->roles()->sync([$superAdminRole->id]);
        $admin->roles()->sync([$adminRole->id]);
        $staff->roles()->sync([$staffRole->id]);

        $this->call([
            ProductSeeder::class,
        ]);

        // Get all users that can order (random + flutter users)
        $orderUsers = $randomUsers->concat([
            \App\Models\User::firstOrCreate([
                'email' => 'agus.nutrifarm@gmail.com'
            ], [
                'name' => 'Agus Pratama',
                'password' => bcrypt('password'),
                'address' => 'Jl. Merdeka No. 123, Jakarta',
            ]),
            \App\Models\User::firstOrCreate([
                'email' => 'sari.nutrifarm@gmail.com'
            ], [
                'name' => 'Sari Dewi',
                'password' => bcrypt('password'),
                'address' => 'Jl. Sudirman No. 45, Bandung',
            ]),
        ]);

        $allProducts = \App\Models\Product::all();
        $statuses = ['pending', 'paid', 'shipped', 'cancelled'];
        $shippingMethods = ['J&T', 'SiCepat', 'JNE', 'AnterAja'];
        $paymentMethods = ['Xendit', 'Midtrans', 'Manual'];
        $faker = \Faker\Factory::create();

        // Generate 10 unique orders
        for ($i = 0; $i < 10; $i++) {
            $user = $orderUsers->random();
            $numProducts = rand(1, min(3, $allProducts->count()));
            $products = $allProducts->random($numProducts);
            $total = 0;
            $invoiceNo = 'INV-' . $faker->unique()->numerify('2025####') . '-' . strtoupper($faker->lexify('???'));
            $order = \App\Models\Order::create([
                'user_id' => $user->id,
                'status' => $faker->randomElement($statuses),
                'shipping_method' => $faker->randomElement($shippingMethods),
                'payment_method' => $faker->randomElement($paymentMethods),
                'payment_status' => $faker->randomElement(['unpaid', 'paid']),
                'resi' => $faker->optional()->bothify('RESI-####-???'),
                'invoice_no' => $invoiceNo,
                'total' => 0, // will update after items
            ]);
            foreach ($products as $product) {
                $qty = rand(1, 5);
                $subtotal = $product->price * $qty;
                $total += $subtotal;
                \App\Models\OrderProduct::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'price' => $product->price,
                ]);
            }
            $order->update(['total' => $total]);
        }
    }
}
