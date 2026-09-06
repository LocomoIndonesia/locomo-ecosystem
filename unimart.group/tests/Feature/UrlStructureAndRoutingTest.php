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
}
