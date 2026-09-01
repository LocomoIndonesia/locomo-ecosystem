<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Vanguard\Models\VanguardParticipant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VanguardOnboardingTest extends TestCase
{
    use RefreshDatabase;

    public function test_vanguard_onboarding_requires_auth()
    {
        $response = $this->get(route('vanguard.register'));
        $response->assertRedirect(route('login'));
    }

    public function test_vanguard_onboarding_can_be_completed()
    {
        $user = User::factory()->create(['role' => 'wisatawan', 'is_verified' => true]);

        $response = $this->actingAs($user)
            ->post(route('vanguard.register.submit'), [
                'profile_type' => 'warga',
                'origin' => 'lokal',
                'is_hpi_candidate' => '1',
                'armada' => 'darat_laut',
                'profession' => 'Tour Guide',
                'initial_rating' => 5,
                'nagari' => 'Carocok Anau',
                'kecamatan' => 'Koto XI Tarusan',
                'kabupaten' => 'Pesisir Selatan',
                'provinsi' => 'Sumatera Barat',
                'postal_code' => '25654',
                'full_address' => 'Jl. Pantai Mandeh No. 12',
                'join_reason' => 'Ingin memajukan pariwisata Mandeh.',
                'gender' => 'L',
                'date_of_birth' => '1995-05-15',
                'selfie' => \Illuminate\Http\UploadedFile::fake()->create('selfie.jpg', 100, 'image/jpeg'),
                'ktp' => \Illuminate\Http\UploadedFile::fake()->create('ktp.jpg', 100, 'image/jpeg'),
                'latitude' => -1.357,
                'longitude' => 100.567,
                'profession_detail' => 'PT Mandeh Jaya',
            ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('vanguard_participants', [
            'user_id' => $user->id,
            'origin' => 'lokal',
            'armada' => 'darat_laut',
            'profile_type' => 'warga',
            'is_hpi_candidate' => true,
            'profession' => 'Tour Guide',
            'initial_rating' => 5,
            'nagari' => 'Carocok Anau',
            'kecamatan' => 'Koto XI Tarusan',
            'kabupaten' => 'Pesisir Selatan',
            'provinsi' => 'Sumatera Barat',
            'postal_code' => '25654',
            'full_address' => 'Jl. Pantai Mandeh No. 12',
            'join_reason' => 'Ingin memajukan pariwisata Mandeh.',
            'profession_detail' => 'PT Mandeh Jaya',
        ]);
    }

    public function test_vanguard_onboarding_darat_laut_quota_limit()
    {
        // Populate 50 slots for darat_laut
        for ($i = 0; $i < 50; $i++) {
            $user = User::factory()->create(['is_verified' => true]);
            VanguardParticipant::create([
                'user_id' => $user->id,
                'tracking_id' => 'VGD-TEST-DL-' . $i,
                'name' => $user->name,
                'origin' => 'lokal',
                'armada' => 'darat_laut',
                'profile_type' => 'warga',
                'is_hpi_candidate' => true,
                'profession' => 'Tour Guide',
                'initial_rating' => 5,
                'nagari' => 'Carocok Anau',
                'kecamatan' => 'Koto XI Tarusan',
                'kabupaten' => 'Pesisir Selatan',
                'provinsi' => 'Sumatera Barat',
                'postal_code' => '25654',
                'full_address' => 'Jl. Pantai Mandeh No. 12',
                'join_reason' => 'Ingin memajukan pariwisata Mandeh.',
            ]);
        }

        // Try registering the 51st slot
        $extraUser = User::factory()->create(['is_verified' => true]);
        $response = $this->actingAs($extraUser)
            ->post(route('vanguard.register.submit'), [
                'profile_type' => 'warga',
                'origin' => 'lokal',
                'is_hpi_candidate' => '1',
                'armada' => 'darat_laut',
                'profession' => 'Tour Guide',
                'initial_rating' => 5,
                'nagari' => 'Carocok Anau',
                'kecamatan' => 'Koto XI Tarusan',
                'kabupaten' => 'Pesisir Selatan',
                'provinsi' => 'Sumatera Barat',
                'postal_code' => '25654',
                'full_address' => 'Jl. Pantai Mandeh No. 12',
                'join_reason' => 'Ingin memajukan pariwisata Mandeh.',
                'gender' => 'L',
                'date_of_birth' => '1995-05-15',
                'selfie' => \Illuminate\Http\UploadedFile::fake()->create('selfie.jpg', 100, 'image/jpeg'),
                'ktp' => \Illuminate\Http\UploadedFile::fake()->create('ktp.jpg', 100, 'image/jpeg'),
                'latitude' => -1.357,
                'longitude' => 100.567,
            ]);

        $response->assertSessionHasErrors('armada');
    }

    public function test_vanguard_onboarding_udara_quota_limit()
    {
        // Populate 100 slots for udara
        for ($i = 0; $i < 100; $i++) {
            $user = User::factory()->create(['is_verified' => true]);
            VanguardParticipant::create([
                'user_id' => $user->id,
                'tracking_id' => 'VGD-TEST-U-' . $i,
                'name' => $user->name,
                'origin' => 'lokal',
                'armada' => 'udara',
                'profile_type' => 'warga',
                'is_hpi_candidate' => true,
                'profession' => 'Tour Guide',
                'initial_rating' => 5,
                'nagari' => 'Carocok Anau',
                'kecamatan' => 'Koto XI Tarusan',
                'kabupaten' => 'Pesisir Selatan',
                'provinsi' => 'Sumatera Barat',
                'postal_code' => '25654',
                'full_address' => 'Jl. Pantai Mandeh No. 12',
                'join_reason' => 'Ingin memajukan pariwisata Mandeh.',
            ]);
        }

        // Try registering the 101st slot
        $extraUser = User::factory()->create(['is_verified' => true]);
        $response = $this->actingAs($extraUser)
            ->post(route('vanguard.register.submit'), [
                'profile_type' => 'warga',
                'origin' => 'lokal',
                'is_hpi_candidate' => '1',
                'armada' => 'udara',
                'profession' => 'Tour Guide',
                'initial_rating' => 5,
                'nagari' => 'Carocok Anau',
                'kecamatan' => 'Koto XI Tarusan',
                'kabupaten' => 'Pesisir Selatan',
                'provinsi' => 'Sumatera Barat',
                'postal_code' => '25654',
                'full_address' => 'Jl. Pantai Mandeh No. 12',
                'join_reason' => 'Ingin memajukan pariwisata Mandeh.',
                'gender' => 'L',
                'date_of_birth' => '1995-05-15',
                'selfie' => \Illuminate\Http\UploadedFile::fake()->create('selfie.jpg', 100, 'image/jpeg'),
                'ktp' => \Illuminate\Http\UploadedFile::fake()->create('ktp.jpg', 100, 'image/jpeg'),
                'latitude' => -1.357,
                'longitude' => 100.567,
            ]);

        $response->assertSessionHasErrors('armada');
    }

    public function test_vanguard_tasks_requires_onboarded_participant()
    {
        $user = User::factory()->create(['is_verified' => true]);
        VanguardParticipant::create([
            'user_id' => $user->id,
            'tracking_id' => 'VGD-TEST-TASKS-1',
            'name' => $user->name,
            'origin' => 'lokal',
            'armada' => 'darat_laut',
            'profile_type' => 'warga',
            'is_hpi_candidate' => true,
            'profession' => 'Tour Guide',
            'initial_rating' => 5,
            'nagari' => 'Carocok Anau',
            'kecamatan' => 'Koto XI Tarusan',
            'kabupaten' => 'Pesisir Selatan',
            'provinsi' => 'Sumatera Barat',
            'postal_code' => '25654',
            'full_address' => 'Jl. Pantai Mandeh No. 12',
            'join_reason' => 'Ingin memajukan pariwisata Mandeh.',
        ]);

        $response = $this->actingAs($user)->get(route('vanguard.tasks'));
        $response->assertStatus(200);
        $response->assertSee('Daftar Tugas');
    }

    public function test_vanguard_supervision_is_accessible()
    {
        $user = User::factory()->create(['is_verified' => true]);
        VanguardParticipant::create([
            'user_id' => $user->id,
            'tracking_id' => 'VGD-TEST-SUPERVISION-1',
            'name' => $user->name,
            'origin' => 'lokal',
            'armada' => 'darat_laut',
            'profile_type' => 'warga',
            'is_hpi_candidate' => true,
            'profession' => 'Tour Guide',
            'initial_rating' => 5,
            'nagari' => 'Carocok Anau',
            'kecamatan' => 'Koto XI Tarusan',
            'kabupaten' => 'Pesisir Selatan',
            'provinsi' => 'Sumatera Barat',
            'postal_code' => '25654',
            'full_address' => 'Jl. Pantai Mandeh No. 12',
            'join_reason' => 'Ingin memajukan pariwisata Mandeh.',
            'is_inaugurated' => true,
        ]);

        $response = $this->actingAs($user)->get(route('vanguard.supervision'));
        $response->assertStatus(200);
        $response->assertSee('Laporan Pengawasan');
    }
}
