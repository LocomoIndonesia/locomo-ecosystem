<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Buat Tenant Sample (Kopi Kita - Paket Premium VIP)
        $tenant = Tenant::create([
            'name' => 'Kopi Kita',
            'domain' => 'kopikita',
            'settings' => [
                'package' => 'Premium VIP',
                'tables_count' => 15,
                'tax_rate' => 10,
                'service_charge' => 5,
                'landing_title' => 'Kopi Kita — Tempat Nongkrong & Diskusi Terbaik',
                'landing_description' => 'Kami menyajikan kopi espresso pilihan beraroma khas Indonesia, roti panggang segar, serta suasana nyaman dengan WiFi super kencang.',
                'instagram_url' => 'https://instagram.com/kopikita.unimart',
                'whatsapp_url' => '6281234567890'
            ]
        ]);

        // 2. Buat User Owner (Ahmad)
        User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Ahmad Owner',
            'email' => 'owner@kopikita.com',
            'password' => Hash::make('owner123'),
            'role' => 'owner',
            'pin' => '1111',
        ]);

        // 3. Buat User Kasir (Budi)
        User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Budi Kasir',
            'email' => 'kasir@kopikita.com',
            'phone' => '081234567891',
            'password' => Hash::make('cashier123'),
            'role' => 'cashier',
            'pin' => '2222',
        ]);

        // 4. Buat User Koki (Chef - Candra)
        User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Candra Koki',
            'email' => 'koki@kopikita.com',
            'password' => Hash::make('chef123'),
            'role' => 'chef',
            'pin' => '3333',
        ]);

        // 5. Buat User Admin Gudang (Agus)
        User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Agus Gudang',
            'email' => 'gudang@kopikita.com',
            'password' => Hash::make('gudang123'),
            'role' => 'admin_gudang',
            'pin' => '4444',
        ]);

        // 6. Buat User Kurir (Koko)
        User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Koko Kurir',
            'email' => 'kurir@kopikita.com',
            'password' => Hash::make('kurir123'),
            'role' => 'kurir',
            'pin' => '5555',
        ]);

        // 7. Buat Tables Sample
        for ($i = 1; $i <= 5; $i++) {
            \App\Models\Table::create([
                'tenant_id' => $tenant->id,
                'name' => 'Meja ' . $i,
                'status' => 'available',
            ]);
        }

        // 8. Buat Products Sample
        \App\Models\Product::create([
            'tenant_id' => $tenant->id,
            'name' => 'Espresso Hot',
            'price' => 18000,
            'stock' => 50,
            'category' => 'Coffee',
        ]);

        \App\Models\Product::create([
            'tenant_id' => $tenant->id,
            'name' => 'Ice Cappuccino',
            'price' => 24000,
            'stock' => 45,
            'category' => 'Coffee',
        ]);

        \App\Models\Product::create([
            'tenant_id' => $tenant->id,
            'name' => 'Butter Croissant',
            'price' => 22000,
            'stock' => 15,
            'category' => 'Bakery',
        ]);

        \App\Models\Product::create([
            'tenant_id' => $tenant->id,
            'name' => 'Chocolate Fudge Muffin',
            'price' => 20000,
            'stock' => 12,
            'category' => 'Bakery',
        ]);

        // 9. Buat Customers Sample
        \App\Models\Customer::create([
            'tenant_id' => $tenant->id,
            'name' => 'Ahmad iZone',
            'phone' => '081234567890',
            'mac_address' => '00:11:22:33:44:55',
            'visit_count' => 1,
        ]);
    }
}
