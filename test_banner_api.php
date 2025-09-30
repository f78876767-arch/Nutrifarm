<?php

// Test Banner API directly
require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Contracts\Console\Kernel;

$app = require_once 'bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

// Test Banner API
echo "Testing Banner API...\n";

try {
    // Get banners using Eloquent directly
    $banners = \App\Models\Banner::active()->ordered()->get();
    
    echo "✅ Banner API Ready!\n";
    echo "Total active banners: " . $banners->count() . "\n";
    
    if ($banners->count() > 0) {
        echo "\nSample banner data:\n";
        foreach ($banners->take(3) as $banner) {
            echo "- ID: " . $banner->id . "\n";
            echo "  Title: " . $banner->title . "\n";
            echo "  Image: " . $banner->image_url . "\n";
            echo "  Active: " . ($banner->is_active ? 'Yes' : 'No') . "\n";
            echo "  Order: " . $banner->sort_order . "\n\n";
        }
    }
    
    // Test API controller
    $controller = new \App\Http\Controllers\Api\BannerController();
    $response = $controller->index();
    $responseData = $response->getData(true);
    
    if ($responseData['success']) {
        echo "✅ API Controller working correctly!\n";
        echo "API returned " . count($responseData['data']) . " banners\n";
    } else {
        echo "❌ API Controller has issues\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error testing Banner API: " . $e->getMessage() . "\n";
}
