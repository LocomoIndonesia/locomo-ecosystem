<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UrlStructureAndRoutingTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_and_local_main_landing_page(): void
    {
        // 1. Akses root di local development
        $responseLocal = $this->get('http://unimart.group.localhost/');
        $responseLocal->assertStatus(200);

        // 2. Akses root di public production
        $responseProd = $this->get('http://unimart.group/');
        $responseProd->assertStatus(200);
    }

    public function test_caffe_subdomain_routing_local_and_prod(): void
    {
        $caffe = Tenant::create([
            'name' => 'Kopi Kita Senja',
            'domain' => 'kopikita',
            'business_type' => 'caffe',
        ]);

        // Local subdomain
        $responseLocal = $this->get('http://kopikita.unimart.group.localhost/');
        $responseLocal->assertStatus(200);
        $responseLocal->assertSee('Kopi Kita Senja');

        // Production subdomain
        $responseProd = $this->get('http://kopikita.unimart.group/');
        $responseProd->assertStatus(200);
        $responseProd->assertSee('Kopi Kita Senja');
    }

    public function test_grosir_subdomain_routing_local_and_prod(): void
    {
        $grosir = Tenant::create([
            'name' => 'Grosir Berkah Jaya',
            'domain' => 'berkahjaya',
            'business_type' => 'grosir',
        ]);

        // Local subdomain
        $responseLocal = $this->get('http://berkahjaya.unimart.group.localhost/');
        $responseLocal->assertStatus(200);
        $responseLocal->assertSee('Grosir Berkah Jaya');

        // Production subdomain
        $responseProd = $this->get('http://berkahjaya.unimart.group/');
        $responseProd->assertStatus(200);
        $responseProd->assertSee('Grosir Berkah Jaya');
    }

    public function test_marketplace_subdomain_routing_local_and_prod(): void
    {
        // Local marketplace subdomain
        $responseLocal = $this->get('http://marketplace.unimart.group.localhost/');
        $responseLocal->assertStatus(200);
        $responseLocal->assertSee('Unimart Marketplace');

        // Production marketplace subdomain
        $responseProd = $this->get('http://marketplace.unimart.group/');
        $responseProd->assertStatus(200);
        $responseProd->assertSee('Unimart Marketplace');
    }

    public function test_tenant_full_domain_attribute(): void
    {
        $tenant = Tenant::create([
            'name' => 'Toko Contoh',
            'domain' => 'tokocontoh',
            'business_type' => 'caffe',
        ]);

        // Pada context request local
        $this->get('http://unimart.group.localhost/');
        $this->assertEquals('tokocontoh.unimart.group.localhost', $tenant->full_domain);

        // Pada context request production
        $this->get('http://unimart.group/');
        $this->assertEquals('tokocontoh.unimart.group', $tenant->full_domain);
    }

    public function test_root_landing_page_never_replaced_by_merchant_landing_even_when_logged_in(): void
    {
        $tenant = Tenant::create([
            'name' => 'Kopi Senja Utama',
            'domain' => 'kopisenja',
            'business_type' => 'caffe',
        ]);

        $owner = \App\Models\User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Owner Kopi',
            'email' => 'owner@kopisenja.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password123'),
            'role' => 'owner',
        ]);

        // 1. Owner logged in mengakses root local domain
        $response = $this->actingAs($owner)->get('http://unimart.group.localhost/');
        $response->assertStatus(200);
        $response->assertSee('Unimart.group');
        $response->assertSee('Ekosistem Bisnis Global Otonom untuk Usaha Modern');

        // 2. Mengakses merchant subdomain lokal
        $subResponse = $this->get('http://kopisenja.unimart.group.localhost/');
        $subResponse->assertStatus(200);
        $subResponse->assertSee('Kopi Senja Utama');
    }
}
