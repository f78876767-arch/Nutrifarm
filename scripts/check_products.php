<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';

/** @var Illuminate\Contracts\Console\Kernel $kernel */
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use App\Models\Product;

$exists = Schema::hasTable('products');
echo "products_table_exists=" . ($exists ? '1' : '0') . PHP_EOL;
if ($exists) {
    $count = Product::count();
    echo "products_count={$count}" . PHP_EOL;
    if ($count > 0) {
        $sample = Product::select('id','name','price','stock_quantity','image_path')->limit(5)->get();
        foreach ($sample as $row) {
            echo json_encode($row->toArray(), JSON_UNESCAPED_UNICODE) . PHP_EOL;
        }
    }
}
