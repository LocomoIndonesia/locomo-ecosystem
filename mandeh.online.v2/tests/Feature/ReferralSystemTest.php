<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Entity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReferralSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_business_registration_automatically_creates_referred_owner()
    {
        $uplineUser = User::factory()->create(['role' => 'wisatawan', 'is_verified' => true]);

        $response = $this->actingAs($uplineUser)
            ->post(route('business.store'), [
                'name' => 'Armada Boat Indah',
                'type' => 'transportasi',
                'description' => 'Boat pariwisata antar pulau',
                'price_range' => 'Rp 200.000',
                'lat' => -1.3470,
                'lng' => 100.5590,
                'owner_name' => 'Doni Driver',
                'owner_email' => 'doni@driver.com',
                'owner_phone' => '081299991111',
                'owner_password' => 'secret123',
                'role_in_business' => 'driver',
                'subscription_package' => 'free',
            ]);

        $response->assertRedirect(route('business.upline'));

        // Verify user creation with upline link
        $this->assertDatabaseHas('users', [
            'name' => 'Doni Driver',
            'email' => 'doni@driver.com',
            'phone_number' => '081299991111',
            'upline_id' => $uplineUser->id,
            'role' => 'wisatawan', // driver is created as general user role
        ]);

        $newDriver = User::where('email', 'doni@driver.com')->first();

        // Verify entity creation
        $this->assertDatabaseHas('entities', [
            'name' => 'Armada Boat Indah',
            'owner_id' => $newDriver->id,
            'upline_id' => $uplineUser->id,
            'type' => 'transportasi',
        ]);

        $entity = Entity::where('name', 'Armada Boat Indah')->first();

        // Verify entity associations
        $this->assertDatabaseHas('entity_associations', [
            'entity_id' => $entity->id,
            'user_id' => $newDriver->id,
            'role_in_business' => 'driver',
        ]);

        // Verify QR code path is saved
        $this->assertNotNull($entity->qr_code_path);
        $this->assertNotNull($entity->qr_token);
    }

    public function test_upline_dashboard_shows_referred_businesses()
    {
        $uplineUser = User::factory()->create(['is_verified' => true]);
        $otherUser = User::factory()->create(['is_verified' => true]);

        // Business 1: Upline referred this
        $downlineUser1 = User::factory()->create(['upline_id' => $uplineUser->id]);
        $biz1 = Entity::create([
            'owner_id' => $downlineUser1->id,
            'upline_id' => $uplineUser->id,
            'name' => 'Referred Shop',
            'slug' => 'referred-shop',
            'type' => 'produk_lokal',
            'qr_token' => 'token1',
        ]);

        // Business 2: Other user referred this
        $biz2 = Entity::create([
            'owner_id' => $otherUser->id,
            'upline_id' => $otherUser->id,
            'name' => 'Other Shop',
            'slug' => 'other-shop',
            'type' => 'produk_lokal',
            'qr_token' => 'token2',
        ]);

        $response = $this->actingAs($uplineUser)
            ->get(route('business.upline'));

        $response->assertStatus(200);
        $response->assertSee('Referred Shop');
        $response->assertDontSee('Other Shop');
    }
}
