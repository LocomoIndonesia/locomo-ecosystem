<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Entity;
use App\Models\Interaction;
use App\Modules\Vanguard\Models\VanguardParticipant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_warga_and_business_owner_dashboards_render_successfully()
    {
        $warga = User::factory()->create(['role' => 'warga', 'is_verified' => true]);
        VanguardParticipant::create([
            'user_id' => $warga->id,
            'tracking_id' => 'WGD-001',
            'name' => $warga->name,
            'origin' => 'lokal',
            'armada' => 'darat_laut',
            'profile_type' => 'warga',
            'is_hpi_candidate' => false,
            'profession' => 'Nelayan',
            'initial_rating' => 4,
            'nagari' => 'Nagari 1',
            'kecamatan' => 'Kecamatan 1',
            'kabupaten' => 'Kabupaten 1',
            'provinsi' => 'Provinsi 1',
            'full_address' => 'Alamat Warga',
            'join_reason' => 'Alasan warga',
            'gender' => 'L',
            'date_of_birth' => '1990-01-01',
        ]);

        $response = $this->actingAs($warga)->get(route('dashboard.warga'));
        $response->assertStatus(200);
        $response->assertSee('Dashboard Warga - Mandeh.Online');

        $bizOwner = User::factory()->create(['role' => 'business_owner', 'is_verified' => true]);
        VanguardParticipant::create([
            'user_id' => $bizOwner->id,
            'tracking_id' => 'WGD-002',
            'name' => $bizOwner->name,
            'origin' => 'lokal',
            'armada' => 'darat_laut',
            'profile_type' => 'pelaku_wisata',
            'is_hpi_candidate' => false,
            'profession' => 'Restaurateur',
            'initial_rating' => 5,
            'nagari' => 'Nagari 2',
            'kecamatan' => 'Kecamatan 2',
            'kabupaten' => 'Kabupaten 2',
            'provinsi' => 'Provinsi 2',
            'full_address' => 'Alamat Owner',
            'join_reason' => 'Alasan owner',
            'gender' => 'P',
            'date_of_birth' => '1992-02-02',
        ]);

        $response2 = $this->actingAs($bizOwner)->get(route('dashboard.business'));
        $response2->assertStatus(200);
        $response2->assertSee('Dashboard Pelaku Wisata - Mandeh.Online');
    }

    public function test_registering_business_for_oneself()
    {
        $user = User::factory()->create(['role' => 'business_owner', 'is_verified' => true]);
        VanguardParticipant::create([
            'user_id' => $user->id,
            'tracking_id' => 'WGD-003',
            'name' => $user->name,
            'origin' => 'lokal',
            'armada' => 'darat_laut',
            'profile_type' => 'pelaku_wisata',
            'is_hpi_candidate' => false,
            'profession' => 'Wiraswasta',
            'initial_rating' => 4,
            'nagari' => 'Nagari 1',
            'kecamatan' => 'Kecamatan 1',
            'kabupaten' => 'Kabupaten 1',
            'provinsi' => 'Provinsi 1',
            'full_address' => 'Alamat 1',
            'join_reason' => 'Alasan 1',
            'gender' => 'L',
            'date_of_birth' => '1990-01-01',
        ]);

        $response = $this->actingAs($user)->post(route('business.store'), [
            'name' => 'Kedai Kopi Saya',
            'type' => 'restoran',
            'description' => 'Kedai kopi nikmat Mandeh',
            'price_range' => 'Rp 10.000',
            'lat' => -1.3470,
            'lng' => 100.5590,
            'register_for_self' => '1',
            'subscription_package' => 'free',
        ]);

        $response->assertRedirect();
        
        // Assert business was created with logged in user as owner
        $this->assertDatabaseHas('entities', [
            'name' => 'Kedai Kopi Saya',
            'owner_id' => $user->id,
            'upline_id' => $user->id,
            'type' => 'restoran',
        ]);

        $entity = Entity::where('name', 'Kedai Kopi Saya')->first();
        
        $this->assertDatabaseHas('entity_associations', [
            'entity_id' => $entity->id,
            'user_id' => $user->id,
            'role_in_business' => 'owner',
        ]);
    }

    public function test_badges_dynamic_calculation()
    {
        $user = User::factory()->create(['role' => 'warga', 'is_verified' => true]);
        $participant = VanguardParticipant::create([
            'user_id' => $user->id,
            'tracking_id' => 'WGD-004',
            'name' => $user->name,
            'origin' => 'lokal',
            'armada' => 'darat_laut',
            'profile_type' => 'warga',
            'is_hpi_candidate' => false,
            'profession' => 'Nelayan',
            'initial_rating' => 3,
            'reputation_points' => 120, // Level 3
            'nagari' => 'Nagari 1',
            'kecamatan' => 'Kecamatan 1',
            'kabupaten' => 'Kabupaten 1',
            'provinsi' => 'Provinsi 1',
            'full_address' => 'Alamat 1',
            'join_reason' => 'Alasan 1',
            'gender' => 'L',
            'date_of_birth' => '1990-01-01',
        ]);

        // Add 3 businesses to get specific badges
        for ($i = 0; $i < 3; $i++) {
            Entity::create([
                'owner_id' => $user->id,
                'upline_id' => $user->id,
                'name' => "Bisnis Ke-{$i}",
                'slug' => "bisnis-ke-{$i}",
                'type' => 'produk_lokal',
                'qr_token' => "token-{$i}",
            ]);
        }

        // Add 1 check-in
        $participant->checkins()->create([
            'checkin_date' => date('Y-m-d'),
            'verified_method' => 'face_to_face',
        ]);

        $badges = $participant->badges;

        $badgeNames = array_column($badges, 'name');

        $this->assertContains('Konsisten Presensi', $badgeNames);
        $this->assertContains('Wirausaha Mandeh', $badgeNames);
        $this->assertContains('Tokoh Komunitas', $badgeNames);
        $this->assertContains('Pelaku Wisata Senior', $badgeNames);
    }

    public function test_qr_code_printing_authorization()
    {
        $owner = User::factory()->create(['role' => 'business_owner', 'is_verified' => true]);
        $upline = User::factory()->create(['role' => 'warga', 'is_verified' => true]);
        $unauthorizedUser = User::factory()->create(['role' => 'wisatawan', 'is_verified' => true]);

        $biz = Entity::create([
            'owner_id' => $owner->id,
            'upline_id' => $upline->id,
            'name' => 'Restoran Upline',
            'slug' => 'restoran-upline',
            'type' => 'restoran',
            'qr_token' => 'random_qr_token',
            'qr_code_path' => 'qrcodes/qr.png',
        ]);

        // 1. Owner can print
        $response1 = $this->actingAs($owner)->get(route('business.qr.print', $biz->id));
        $response1->assertStatus(200);
        $response1->assertSee('Cetak QR Code - Restoran Upline');

        // 2. Upline can print
        $response2 = $this->actingAs($upline)->get(route('business.qr.print', $biz->id));
        $response2->assertStatus(200);

        // 3. Random user cannot print (403)
        $response3 = $this->actingAs($unauthorizedUser)->get(route('business.qr.print', $biz->id));
        $response3->assertStatus(403);
    }

    public function test_business_registration_with_paid_package_requires_payment_proof_and_awaits_admin_verification()
    {
        $user = User::factory()->create(['role' => 'business_owner', 'is_verified' => true]);
        $admin = User::factory()->create(['role' => 'admin', 'is_verified' => true]);

        VanguardParticipant::create([
            'user_id' => $user->id,
            'tracking_id' => 'WGD-999',
            'name' => $user->name,
            'origin' => 'lokal',
            'armada' => 'darat_laut',
            'profile_type' => 'pelaku_wisata',
            'is_hpi_candidate' => false,
            'profession' => 'Wiraswasta',
            'initial_rating' => 4,
            'nagari' => 'Nagari 1',
            'kecamatan' => 'Kecamatan 1',
            'kabupaten' => 'Kabupaten 1',
            'provinsi' => 'Provinsi 1',
            'full_address' => 'Alamat 1',
            'join_reason' => 'Alasan 1',
            'gender' => 'L',
            'date_of_birth' => '1990-01-01',
        ]);

        // Mock upload file
        \Illuminate\Support\Facades\Storage::fake('public');
        $file = \Illuminate\Http\UploadedFile::fake()->create('proof.jpg', 500);

        // 1. Register with VIP package and proof
        $response = $this->actingAs($user)->post(route('business.store'), [
            'name' => 'Hotel VIP Mewah',
            'type' => 'hotel',
            'description' => 'Hotel bintang lima di Mandeh',
            'price_range' => 'Rp 1.000.000',
            'lat' => -1.3470,
            'lng' => 100.5590,
            'register_for_self' => '1',
            'subscription_package' => 'vip',
            'payment_proof' => $file,
        ]);

        $response->assertRedirect();
        
        // Assert business is registered but is_active = false
        $this->assertDatabaseHas('entities', [
            'name' => 'Hotel VIP Mewah',
            'subscription_package' => 'vip',
            'is_active' => false,
        ]);

        $entity = Entity::where('name', 'Hotel VIP Mewah')->first();
        $this->assertNotNull($entity->payment_proof_path);

        // 2. Admin moderates and approves
        $responseAdmin = $this->actingAs($admin)->post(route('admin.business.verification.verify', $entity->id), [
            'decision' => 'approve',
        ]);

        $responseAdmin->assertRedirect();

        // Assert business is now active
        $this->assertDatabaseHas('entities', [
            'id' => $entity->id,
            'is_active' => true,
        ]);
        $this->assertNotNull($entity->fresh()->payment_verified_at);
    }
}
