<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Vanguard\Models\VanguardSurvey;
use App\Modules\Vanguard\Models\VanguardParticipant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminManagerDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_cannot_access_admin_manager_dashboard()
    {
        $user = User::factory()->create(['role' => 'wisatawan']);

        $response = $this->actingAs($user)
            ->get(route('vanguard.admin.surveys'));

        $response->assertStatus(403);
    }

    public function test_admin_manager_can_access_dashboard()
    {
        $admin = User::factory()->create(['role' => 'admin_manager', 'is_verified' => true]);

        $response = $this->actingAs($admin)
            ->get(route('vanguard.admin.surveys'));

        $response->assertStatus(200);
        $response->assertSee('Admin Manager Dashboard');
    }

    public function test_admin_manager_can_verify_unverified_user()
    {
        $admin = User::factory()->create(['role' => 'admin_manager', 'is_verified' => true]);
        $unverifiedUser = User::factory()->create(['is_verified' => false]);

        $response = $this->actingAs($admin)
            ->post(route('vanguard.admin.verify', $unverifiedUser->id));

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $unverifiedUser->id,
            'is_verified' => true,
        ]);
    }

    public function test_unverified_user_can_submit_onboarding_questionnaire()
    {
        $user = User::factory()->create(['is_verified' => false]);

        $response = $this->actingAs($user)
            ->post(route('vanguard.register.submit'), [
                'profile_type' => 'warga',
                'origin' => 'lokal',
                'is_hpi_candidate' => '1',
                'armada' => 'darat_laut',
                'profession' => 'Nelayan',
                'initial_rating' => 5,
                'nagari' => 'Carocok Anau',
                'kecamatan' => 'Koto XI Tarusan',
                'kabupaten' => 'Pesisir Selatan',
                'provinsi' => 'Sumatera Barat',
                'postal_code' => '25654',
                'full_address' => 'Jl. Pantai Mandeh No. 12',
                'join_reason' => 'Ingin bergabung.',
                'gender' => 'L',
                'date_of_birth' => '1995-05-15',
                'selfie' => \Illuminate\Http\UploadedFile::fake()->create('selfie.jpg', 100, 'image/jpeg'),
                'ktp' => \Illuminate\Http\UploadedFile::fake()->create('ktp.jpg', 100, 'image/jpeg'),
                'latitude' => -1.2186,
                'longitude' => 100.4264,
            ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('user_log_tracking', [
            'user_id' => $user->id,
            'latitude' => -1.2186,
            'longitude' => 100.4264,
        ]);
        $this->assertDatabaseHas('vanguard_participants', [
            'user_id' => $user->id,
            'profile_type' => 'warga',
            'is_hpi_candidate' => true,
            'profession' => 'Nelayan',
            'initial_rating' => 5,
            'nagari' => 'Carocok Anau',
            'kecamatan' => 'Koto XI Tarusan',
            'kabupaten' => 'Pesisir Selatan',
            'provinsi' => 'Sumatera Barat',
            'postal_code' => '25654',
            'full_address' => 'Jl. Pantai Mandeh No. 12',
            'join_reason' => 'Ingin bergabung.',
        ]);
        
        $user->refresh();
        $this->assertFalse((bool)$user->is_verified);
    }

    public function test_admin_manager_can_inaugurate_graduated_vanguard()
    {
        $admin = User::factory()->create(['role' => 'admin_manager', 'is_verified' => true]);
        $user = User::factory()->create(['is_verified' => true]);
        
        $participant = VanguardParticipant::create([
            'user_id' => $user->id,
            'tracking_id' => 'VGD-TEST-GRAD',
            'name' => $user->name,
            'origin' => 'lokal',
            'armada' => 'darat_laut',
            'reputation_points' => 85,
            'is_vanguard_graduated' => true,
            'is_inaugurated' => false,
        ]);

        $response = $this->actingAs($admin)
            ->post(route('vanguard.admin.inaugurate'), [
                'participant_id' => $participant->id,
            ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('vanguard_participants', [
            'id' => $participant->id,
            'is_inaugurated' => true,
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'role' => 'layanan_publik', // Promoted to officer
        ]);
    }

    public function test_admin_manager_can_approve_vanguard_report()
    {
        $admin = User::factory()->create(['role' => 'admin_manager', 'is_verified' => true]);
        $user = User::factory()->create(['is_verified' => true]);
        
        $participant = VanguardParticipant::create([
            'user_id' => $user->id,
            'tracking_id' => 'VGD-TEST-REP',
            'name' => $user->name,
            'origin' => 'lokal',
            'armada' => 'darat_laut',
            'reputation_points' => 0,
        ]);

        $mission = \App\Modules\Vanguard\Models\VanguardMission::create([
            'title' => 'Test Mission',
            'description' => 'Test mission description',
            'armada_type' => 'both',
            'points_reward' => 25,
        ]);

        $report = \App\Modules\Vanguard\Models\VanguardMissionReport::create([
            'participant_id' => $participant->id,
            'mission_id' => $mission->id,
            'status' => 'pending',
            'report_text' => 'Pelaksanaan tugas oleh kader.',
            'points_awarded' => 25,
        ]);

        $response = $this->actingAs($admin)
            ->post(route('vanguard.admin.reports.approve', $report->id));

        $response->assertRedirect();
        $this->assertDatabaseHas('vanguard_mission_reports', [
            'id' => $report->id,
            'status' => 'approved',
        ]);

        $participant->refresh();
        $this->assertEquals(25, $participant->reputation_points);
        $this->assertFalse($participant->is_vanguard_graduated);
    }

    public function test_admin_manager_can_reject_vanguard_report()
    {
        $admin = User::factory()->create(['role' => 'admin_manager', 'is_verified' => true]);
        $user = User::factory()->create(['is_verified' => true]);
        
        $participant = VanguardParticipant::create([
            'user_id' => $user->id,
            'tracking_id' => 'VGD-TEST-REP2',
            'name' => $user->name,
            'origin' => 'lokal',
            'armada' => 'darat_laut',
            'reputation_points' => 10,
        ]);

        $mission = \App\Modules\Vanguard\Models\VanguardMission::create([
            'title' => 'Test Mission 2',
            'description' => 'Test mission description 2',
            'armada_type' => 'both',
            'points_reward' => 25,
        ]);

        $report = \App\Modules\Vanguard\Models\VanguardMissionReport::create([
            'participant_id' => $participant->id,
            'mission_id' => $mission->id,
            'status' => 'pending',
            'report_text' => 'Pelaksanaan tugas oleh kader 2.',
            'points_awarded' => 25,
        ]);

        $response = $this->actingAs($admin)
            ->post(route('vanguard.admin.reports.reject', $report->id));

        $response->assertRedirect();
        $this->assertDatabaseHas('vanguard_mission_reports', [
            'id' => $report->id,
            'status' => 'rejected',
            'points_awarded' => 0,
        ]);

        $participant->refresh();
        $this->assertEquals(0, $participant->reputation_points);
    }

    public function test_admin_manager_can_update_user_role()
    {
        $admin = User::factory()->create(['role' => 'admin_manager', 'is_verified' => true]);
        $user = User::factory()->create(['role' => 'wisatawan']);

        $response = $this->actingAs($admin)
            ->post(route('vanguard.admin.users.role', $user->id), [
                'role' => 'layanan_publik',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'role' => 'layanan_publik',
        ]);
    }
}
