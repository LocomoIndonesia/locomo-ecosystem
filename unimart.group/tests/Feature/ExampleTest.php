<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Tenant;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        Tenant::create([
            'name' => 'Kopi Kita',
            'domain' => 'localhost',
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
