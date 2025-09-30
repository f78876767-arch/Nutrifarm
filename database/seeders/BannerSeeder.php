<?php

namespace Database\Seeders;

use App\Models\Banner;
use Illuminate\Database\Seeder;

class BannerSeeder extends Seeder
{
    public function run()
    {
        Banner::create([
            'title' => 'Promo Spesial Produk Organik',
            'image_url' => 'https://picsum.photos/1200/400?random=1',
            'description' => 'Diskon hingga 30% untuk semua produk organik',
            'action_url' => 'nutrifarm://categories/organic',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        Banner::create([
            'title' => 'Produk Baru Telah Hadir',
            'image_url' => 'https://picsum.photos/1200/400?random=2',
            'description' => 'Jelajahi koleksi produk terbaru kami',
            'action_url' => 'nutrifarm://new-products',
            'is_active' => true,
            'sort_order' => 2,
        ]);

        Banner::create([
            'title' => 'Flash Sale Hari Ini',
            'image_url' => 'https://picsum.photos/1200/400?random=3',
            'description' => 'Buruan! Flash sale hanya hari ini dengan potongan hingga 50%',
            'action_url' => 'nutrifarm://flash-sale',
            'is_active' => true,
            'sort_order' => 3,
        ]);

        Banner::create([
            'title' => 'Program Membership Premium',
            'image_url' => 'https://picsum.photos/1200/400?random=4',
            'description' => 'Daftar sekarang dan dapatkan berbagai keuntungan eksklusif',
            'action_url' => 'nutrifarm://membership',
            'is_active' => true,
            'sort_order' => 4,
        ]);

        Banner::create([
            'title' => 'Gratis Ongkir se-Indonesia',
            'image_url' => 'https://picsum.photos/1200/400?random=5',
            'description' => 'Minimal belanja Rp 100.000 gratis ongkir ke seluruh Indonesia',
            'action_url' => 'nutrifarm://free-shipping',
            'is_active' => true,
            'sort_order' => 5,
        ]);
    }
}
