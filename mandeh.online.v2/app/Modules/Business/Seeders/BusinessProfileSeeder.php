<?php

namespace App\Modules\Business\Seeders;

use App\Modules\Business\Models\Business;
use App\Modules\Business\Models\Product;
use App\Modules\Business\Models\MenuItem;
use App\Models\User;
use Illuminate\Database\Seeder;

class BusinessProfileSeeder extends Seeder
{
    public function run()
    {
        $user = User::first() ?? User::factory()->create();

        // 1. Mandeh Islands Resort (Sponsor/Premium Wisma)
        $resort = Business::updateOrCreate(
            ['slug' => 'mandeh-islands'],
            [
                'user_id' => $user->id,
                'name' => 'Mandeh Islands Resort',
                'category' => 'Wisma',
                'tier' => 'sponsor',
                'description' => 'Resort eksklusif dengan kenyamanan premium dan akses langsung ke pantai pribadi.',
                'image' => 'https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?q=80&w=1200',
                'geolocation' => ['lat' => -1.2345, 'lng' => 100.4567, 'address' => 'Pulau Sirandah'],
                'contact' => ['phone' => '08123456789'],
                'verification_status' => 'verified',
            ]
        );

        // 2. Pandan View Mandeh (Sponsor/Premium Resto)
        $resto = Business::updateOrCreate(
            ['slug' => 'pandan-view'],
            [
                'user_id' => $user->id,
                'name' => 'Pandan View Mandeh',
                'category' => 'Resto',
                'tier' => 'sponsor',
                'description' => 'Nikmati kuliner khas Minang dengan pemandangan Teluk Mandeh yang spektakuler.',
                'image' => 'https://images.unsplash.com/photo-1514362545857-3bc16c4c7d1b?q=80&w=1200',
                'geolocation' => ['lat' => -1.2456, 'lng' => 100.4678, 'address' => 'Puncak Pandan'],
                'contact' => ['phone' => '08129876543'],
                'verification_status' => 'verified',
            ]
        );

        // 3. Toko Souvenir Mandeh (Sponsor/Premium Shop)
        $shop = Business::updateOrCreate(
            ['slug' => 'souvenir-mandeh'],
            [
                'user_id' => $user->id,
                'name' => 'Toko Souvenir Mandeh',
                'category' => 'Toko',
                'tier' => 'sponsor',
                'description' => 'Pusat oleh-oleh dan kerajinan tangan asli warga Mandeh.',
                'image' => 'https://images.unsplash.com/photo-1590736962031-2947702f231e?q=80&w=1200',
                'geolocation' => ['lat' => -1.2567, 'lng' => 100.4789, 'address' => 'Dermaga Tarusan'],
                'contact' => ['phone' => '08112233445'],
                'verification_status' => 'verified',
            ]
        );

        // 4. Parkiran Mandeh 01 (Free Business)
        Business::updateOrCreate(
            ['slug' => 'parkiran-mandeh-01'],
            [
                'user_id' => $user->id,
                'name' => 'Parkiran Mandeh 01',
                'category' => 'Parkir',
                'tier' => 'free',
                'description' => 'Area parkir resmi dan aman untuk pengunjung.',
                'geolocation' => ['lat' => -1.2678, 'lng' => 100.4890, 'address' => 'Pintu Masuk Kawasan'],
                'contact' => ['phone' => '08134455667'],
                'verification_status' => 'verified',
            ]
        );

        // Seed Menu Items for Resto
        MenuItem::updateOrCreate(
            ['business_id' => $resto->id, 'name' => 'Rendang Lokan'],
            [
                'description' => 'Kerang sungai khas Pesisir dimasak rendang.',
                'price' => 45000,
                'category' => 'Makanan',
                'is_best_seller' => true,
                'image' => 'https://images.unsplash.com/photo-1606787366850-de6330128bfc?q=80&w=200',
            ]
        );
        MenuItem::updateOrCreate(
            ['business_id' => $resto->id, 'name' => 'Sate Padang Pariaman'],
            [
                'description' => 'Sate daging dengan kuah kuning kental.',
                'price' => 30000,
                'category' => 'Makanan',
                'image' => 'https://images.unsplash.com/photo-1544025162-d76694265947?q=80&w=200',
            ]
        );

        // Seed Products for Shop
        Product::updateOrCreate(
            ['business_id' => $shop->id, 'name' => 'Batik Tanah Liek'],
            [
                'description' => 'Batik legendaris Minangkabau dengan pewarna tanah.',
                'price' => 250000,
                'image' => 'https://images.unsplash.com/photo-1590736962031-2947702f231e?q=80&w=200',
            ]
        );
        Product::updateOrCreate(
            ['business_id' => $shop->id, 'name' => 'Sulaman Bayang'],
            [
                'description' => 'Sulam tangan halus khas Kenagarian Bayang.',
                'price' => 150000,
                'image' => 'https://images.unsplash.com/photo-1605106702734-205df224ecce?q=80&w=200',
            ]
        );
    }
}
