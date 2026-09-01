<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Vanguard\Models\VanguardParticipant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TourismOnboardingTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_register_form_is_accessible()
    {
        $response = $this->get(route('register'));
        $response->assertStatus(200);
        $response->assertSee('Pendaftaran Akun');
    }

    public function test_public_register_submits_successfully_for_tourist()
    {
        $response = $this->post(route('register.submit'), [
            'name' => 'Budi Wisatawan',
            'email' => 'budi@example.com',
            'phone_number' => '081234567890',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'profile_type' => 'wisatawan',
            'origin' => 'perantau',
            'is_hpi_candidate' => '1', // Test that it forces to 0 because profile_type is wisatawan
            'armada' => 'darat_laut',
            'profession' => 'Karyawan Swasta',
            'initial_rating' => 2,
            'nagari' => 'Carocok Anau',
            'kecamatan' => 'Koto XI Tarusan',
            'kabupaten' => 'Pesisir Selatan',
            'provinsi' => 'Sumatera Barat',
            'postal_code' => '25654',
            'full_address' => 'Hotel Mandeh Indah Room 101',
            'join_reason' => 'Ingin liburan ke Mandeh.',
            'gender' => 'L',
            'date_of_birth' => '1990-08-20',
            'selfie' => \Illuminate\Http\UploadedFile::fake()->create('selfie.jpg', 100, 'image/jpeg'),
            'ktp' => \Illuminate\Http\UploadedFile::fake()->create('ktp.jpg', 100, 'image/jpeg'),
            'latitude' => -1.3470,
            'longitude' => 100.5590,
        ]);

        $response->assertRedirect(route('dashboard'));
        
        $user = User::where('email', 'budi@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('wisatawan', $user->role);
        $this->assertFalse((bool)$user->is_verified);

        $this->assertDatabaseHas('vanguard_participants', [
            'user_id' => $user->id,
            'profile_type' => 'wisatawan',
            'is_hpi_candidate' => false, // Forced to false
            'profession' => 'Karyawan Swasta',
            'origin' => 'perantau',
        ]);
    }

    public function test_authenticated_onboarding_form_requires_auth()
    {
        $response = $this->get(route('tourism.register'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_onboarding_form_is_accessible()
    {
        $user = User::factory()->create(['role' => 'warga', 'is_verified' => true]);
        $response = $this->actingAs($user)->get(route('tourism.register'));
        $response->assertStatus(200);
        $response->assertSee('Lengkapi Onboarding Akun');
    }

    public function test_authenticated_onboarding_submits_successfully_for_warga()
    {
        $user = User::factory()->create(['role' => 'warga', 'is_verified' => true]);

        $response = $this->actingAs($user)
            ->post(route('tourism.register.submit'), [
                'profile_type' => 'warga',
                'origin' => 'lokal',
                'is_hpi_candidate' => '1',
                'armada' => 'darat_laut',
                'profession' => 'Nelayan',
                'initial_rating' => 3,
                'nagari' => 'Carocok Anau',
                'kecamatan' => 'Koto XI Tarusan',
                'kabupaten' => 'Pesisir Selatan',
                'provinsi' => 'Sumatera Barat',
                'postal_code' => '25654',
                'full_address' => 'Desa Wisata Mandeh',
                'join_reason' => 'Ingin ikut menjaga laut Mandeh.',
                'gender' => 'L',
                'date_of_birth' => '1985-04-12',
                'selfie' => \Illuminate\Http\UploadedFile::fake()->create('selfie.jpg', 100, 'image/jpeg'),
                'ktp' => \Illuminate\Http\UploadedFile::fake()->create('ktp.jpg', 100, 'image/jpeg'),
                'latitude' => -1.3470,
                'longitude' => 100.5590,
            ]);

        $response->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('vanguard_participants', [
            'user_id' => $user->id,
            'profile_type' => 'warga',
            'is_hpi_candidate' => true,
            'profession' => 'Nelayan',
            'origin' => 'lokal',
            'armada' => 'darat_laut',
        ]);
        
        $user->refresh();
        $this->assertEquals('warga', $user->role);
    }

    public function test_dashboards_redirect_to_onboarding_if_not_registered()
    {
        $wargaUser = User::factory()->create(['role' => 'warga', 'is_verified' => true]);
        $response = $this->actingAs($wargaUser)->get(route('dashboard.warga'));
        $response->assertRedirect(route('tourism.register'));

        $wisatawanUser = User::factory()->create(['role' => 'wisatawan', 'is_verified' => true]);
        $response = $this->actingAs($wisatawanUser)->get(route('dashboard.wisatawan'));
        $response->assertRedirect(route('tourism.register'));

        $businessUser = User::factory()->create(['role' => 'business_owner', 'is_verified' => true]);
        $response = $this->actingAs($businessUser)->get(route('dashboard.business'));
        $response->assertRedirect(route('tourism.register'));
    }
}
