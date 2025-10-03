<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Banner;

echo "DB Connection: ".config('database.default').PHP_EOL;
try {
    $total = DB::table('banners')->count();
    $active = DB::table('banners')->where('is_active',1)->count();
    echo "Total banners: $total".PHP_EOL;
    echo "Active banners: $active".PHP_EOL;
    if($total){
        $sample = Banner::first();
        echo "Sample banner: ".json_encode($sample->only(['id','title','is_active','image_url','sort_order'])).PHP_EOL;
    }
} catch (Exception $e) {
    echo 'Error: '.$e->getMessage().PHP_EOL;
}
