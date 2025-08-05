<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $examples = [
            ['name' => 'Fruits'],
            ['name' => 'Vegetables'],
            ['name' => 'Dairy'],
            ['name' => 'Bakery'],
            ['name' => 'Beverages'],
        ];
        foreach ($examples as $data) {
            Category::firstOrCreate(['name' => $data['name']]);
        }
    }
}
