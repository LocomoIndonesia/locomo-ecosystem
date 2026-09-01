<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TenantIsolationAndAuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Uji isolasi data antar tenant (Global Scope Tenant).
     */
    public function test_tenant_data_isolation(): void
    {
        // 1. Buat Tenant A dan Tenant B
        $tenantA = Tenant::create([
            'name' => 'Cafe Coffee A',
            'domain' => 'cafe-a.localhost',
        ]);

        $tenantB = Tenant::create([
            'name' => 'Resto Grill B',
            'domain' => 'resto-b.localhost',
        ]);

        // 2. Set tenant_id aktif ke Tenant A
        app()->instance('tenant_id', $tenantA->id);

        // Buat produk untuk Tenant A (tenant_id otomatis terisi lewat trait BelongsToTenant)
        Product::create([
            'name' => 'Kopi Latte A',
            'price' => 20000,
            'category' => 'minuman',
            'stock' => 10,
        ]);

        // 3. Set tenant_id aktif ke Tenant B
        app()->instance('tenant_id', $tenantB->id);

        // Buat produk untuk Tenant B
        Product::create([
            'name' => 'Steak Grill B',
            'price' => 75000,
            'category' => 'makanan',
            'stock' => 5,
        ]);

        // 4. Verifikasi isolasi query saat Tenant A aktif
        app()->instance('tenant_id', $tenantA->id);
        $productsA = Product::all();
        
        $this->assertCount(1, $productsA);
        $this->assertEquals('Kopi Latte A', $productsA->first()->name);

        // 5. Verifikasi isolasi query saat Tenant B aktif
        app()->instance('tenant_id', $tenantB->id);
        $productsB = Product::all();

        $this->assertCount(1, $productsB);
        $this->assertEquals('Steak Grill B', $productsB->first()->name);
    }

    /**
     * Uji login staf menggunakan email.
     */
    public function test_staff_login_with_email(): void
    {
        // 1. Setup Tenant
        $tenant = Tenant::create([
            'name' => 'Unimart Cafe',
            'domain' => 'unimart.localhost',
        ]);

        app()->instance('tenant_id', $tenant->id);

        // 2. Buat user staf
        $staff = User::create([
            'name' => 'Budi Kasir',
            'role' => 'cashier',
            'email' => 'budi@kopikita.com',
            'password' => \Illuminate\Support\Facades\Hash::make('cashier123'),
        ]);

        // 3. Simulasikan HTTP request ke domain tenant
        $response = $this->withHeaders([
            'Host' => 'unimart.localhost',
        ])->post('/login', [
            'login' => 'budi@kopikita.com',
            'password' => 'cashier123',
        ]);

        // Harus me-redirect ke dashboard staf
        $response->assertRedirect('/staff/dashboard');
        $this->assertAuthenticatedAs($staff);
    }

    /**
     * Uji login staf menggunakan No. HP.
     */
    public function test_staff_login_with_phone(): void
    {
        // 1. Setup Tenant
        $tenant = Tenant::create([
            'name' => 'Unimart Cafe',
            'domain' => 'unimart.localhost',
        ]);

        app()->instance('tenant_id', $tenant->id);

        // 2. Buat user staf
        $staff = User::create([
            'name' => 'Budi Kasir',
            'role' => 'cashier',
            'phone' => '081234567891',
            'password' => \Illuminate\Support\Facades\Hash::make('cashier123'),
        ]);

        // 3. Simulasikan HTTP request ke domain tenant
        $response = $this->withHeaders([
            'Host' => 'unimart.localhost',
        ])->post('/login', [
            'login' => '081234567891',
            'password' => 'cashier123',
        ]);

        // Harus me-redirect ke dashboard staf
        $response->assertRedirect('/staff/dashboard');
        $this->assertAuthenticatedAs($staff);
    }

    /**
     * Uji login staf gagal dengan password salah.
     */
    public function test_staff_login_fails_with_invalid_credentials(): void
    {
        $tenant = Tenant::create([
            'name' => 'Unimart Cafe',
            'domain' => 'unimart.localhost',
        ]);

        app()->instance('tenant_id', $tenant->id);

        User::create([
            'name' => 'Budi Kasir',
            'role' => 'cashier',
            'email' => 'budi@kopikita.com',
            'password' => \Illuminate\Support\Facades\Hash::make('cashier123'),
        ]);

        // Kirim password yang salah
        $response = $this->withHeaders([
            'Host' => 'unimart.localhost',
        ])->post('/login', [
            'login' => 'budi@kopikita.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    /**
     * Uji otorisasi: Kasir tidak boleh mengakses Dashboard Owner.
     */
    public function test_cashier_cannot_access_owner_dashboard(): void
    {
        $tenant = Tenant::create([
            'name' => 'Unimart Cafe',
            'domain' => 'unimart.localhost',
        ]);

        app()->instance('tenant_id', $tenant->id);

        $cashier = User::create([
            'name' => 'Budi Kasir',
            'role' => 'cashier',
            'email' => 'budi@kopikita.com',
            'password' => \Illuminate\Support\Facades\Hash::make('cashier123'),
        ]);

        $response = $this->actingAs($cashier)
                         ->withHeaders(['Host' => 'unimart.localhost'])
                         ->get('/owner/dashboard');

        $response->assertStatus(403);
    }

    /**
     * Uji otorisasi: Owner bisa mengakses Dashboard Owner.
     */
    public function test_owner_can_access_owner_dashboard(): void
    {
        $tenant = Tenant::create([
            'name' => 'Unimart Cafe',
            'domain' => 'unimart.localhost',
        ]);

        app()->instance('tenant_id', $tenant->id);

        $owner = User::create([
            'name' => 'Ahmad Owner',
            'role' => 'owner',
            'email' => 'owner@kopikita.com',
            'password' => \Illuminate\Support\Facades\Hash::make('owner123'),
        ]);

        $response = $this->actingAs($owner)
                         ->withHeaders(['Host' => 'unimart.localhost'])
                         ->get('/owner/dashboard');

        $response->assertStatus(200);
    }
}
