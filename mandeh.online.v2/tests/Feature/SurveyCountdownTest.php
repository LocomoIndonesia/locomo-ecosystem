<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Vanguard\Models\VanguardSurvey;
use App\Modules\Vanguard\Models\VanguardParticipant;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SurveyCountdownTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_manager_can_create_survey()
    {
        $admin = User::factory()->create(['role' => 'admin_manager']);

        $response = $this->actingAs($admin)
            ->post(route('vanguard.admin.surveys.store'), [
                'title' => 'Pemilihan Ketua DPC HPI',
                'description' => 'Memilih ketua baru',
                'target_roles' => ['vanguard', 'warga'],
                'options' => [
                    ['title' => 'Kandidat A', 'description' => 'Desc A', 'profile_url' => 'http://example.com/a', 'link_text' => 'Bio A'],
                    ['title' => 'Kandidat B', 'description' => 'Desc B', 'profile_url' => 'http://example.com/b', 'link_text' => 'Bio B'],
                ],
                'start_time' => Carbon::now()->addDays(2)->toDateTimeString(),
                'end_time' => Carbon::now()->addDays(5)->toDateTimeString(),
            ]);

        $response->assertStatus(302); // Redirect back
        $this->assertDatabaseHas('vanguard_surveys', [
            'title' => 'Pemilihan Ketua DPC HPI',
        ]);
    }

    public function test_admin_cannot_edit_active_survey_due_to_mutability_lock()
    {
        $admin = User::factory()->create(['role' => 'admin_manager']);

        // Survey that is already active (started in the past)
        $survey = VanguardSurvey::create([
            'title' => 'Active Survey',
            'description' => 'This survey has started',
            'target_roles' => ['all'],
            'options' => [
                ['title' => 'Kandidat A'],
                ['title' => 'Kandidat B'],
            ],
            'start_time' => Carbon::now()->subHour(),
            'end_time' => Carbon::now()->addDay(),
        ]);

        $response = $this->actingAs($admin)
            ->post(route('vanguard.admin.surveys.update', $survey->id), [
                'title' => 'Modified Title',
                'description' => 'This should fail',
                'target_roles' => ['all'],
                'options' => [
                    ['title' => 'Kandidat A'],
                    ['title' => 'Kandidat C'],
                ],
                'start_time' => Carbon::now()->addHour()->toDateTimeString(),
                'end_time' => Carbon::now()->addDays(2)->toDateTimeString(),
            ]);

        $response->assertSessionHasErrors('start_time');
        $this->assertDatabaseHas('vanguard_surveys', [
            'id' => $survey->id,
            'title' => 'Active Survey', // untouched
        ]);
    }

    public function test_admin_can_edit_scheduled_survey_before_start()
    {
        $admin = User::factory()->create(['role' => 'admin_manager']);

        // Survey that has not started yet (scheduled in the future)
        $survey = VanguardSurvey::create([
            'title' => 'Scheduled Survey',
            'description' => 'This survey will start in future',
            'target_roles' => ['all'],
            'options' => [
                ['title' => 'Kandidat A'],
                ['title' => 'Kandidat B'],
            ],
            'start_time' => Carbon::now()->addDays(2),
            'end_time' => Carbon::now()->addDays(4),
        ]);

        $response = $this->actingAs($admin)
            ->post(route('vanguard.admin.surveys.update', $survey->id), [
                'title' => 'Modified Title',
                'description' => 'This should succeed',
                'target_roles' => ['all'],
                'options' => [
                    ['title' => 'Kandidat A'],
                    ['title' => 'Kandidat C'],
                ],
                'start_time' => Carbon::now()->addDays(3)->toDateTimeString(),
                'end_time' => Carbon::now()->addDays(6)->toDateTimeString(),
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('vanguard_surveys', [
            'id' => $survey->id,
            'title' => 'Modified Title',
        ]);
    }

    public function test_voter_can_cast_and_change_vote_before_ended()
    {
        $user = User::factory()->create(['role' => 'wisatawan', 'is_verified' => true]);
        $participant = VanguardParticipant::create([
            'user_id' => $user->id,
            'tracking_id' => 'VGD-TEST-VOTE',
            'name' => $user->name,
            'origin' => 'lokal',
            'armada' => 'darat_laut',
            'reputation_points' => 85,
            'is_vanguard_graduated' => true,
        ]);

        $survey = VanguardSurvey::create([
            'title' => 'Survey Pilihan',
            'description' => 'Pilih salah satu',
            'target_roles' => ['vanguard'],
            'options' => [
                ['title' => 'Opsi A'],
                ['title' => 'Opsi B'],
            ],
            'start_time' => Carbon::now()->subMinutes(10),
            'end_time' => Carbon::now()->addHours(2),
        ]);

        // Cast vote first time
        $response1 = $this->actingAs($user)
            ->post(route('vanguard.voting.submit'), [
                'survey_id' => $survey->id,
                'choice' => 'Opsi A',
            ]);
        $response1->assertRedirect();
        
        $this->assertDatabaseHas('vanguard_vote_participations', [
            'survey_id' => $survey->id,
            'user_id' => $user->id,
        ]);

        // Decrypt the vote in DB to verify it was cast
        $voterHash = hash('sha256', $participant->tracking_id . $survey->id . config('app.key'));
        $vote1 = \App\Modules\Vanguard\Models\VanguardVote::where('voter_hash', $voterHash)->first();
        $this->assertNotNull($vote1);
        $this->assertEquals('Opsi A', $vote1->getChoice());

        // Change vote
        $response2 = $this->actingAs($user)
            ->post(route('vanguard.voting.submit'), [
                'survey_id' => $survey->id,
                'choice' => 'Opsi B',
            ]);
        $response2->assertRedirect();

        // Verify vote updated in place and choice has changed
        $vote2 = \App\Modules\Vanguard\Models\VanguardVote::where('voter_hash', $voterHash)->first();
        $this->assertEquals('Opsi B', $vote2->getChoice());
        
        // Ensure count of total votes is still 1
        $this->assertEquals(1, \App\Modules\Vanguard\Models\VanguardVote::where('survey_id', $survey->id)->count());
    }

    public function test_admin_can_edit_scheduled_mission_before_start()
    {
        $admin = User::factory()->create(['role' => 'admin_manager']);
        $mission = \App\Modules\Vanguard\Models\VanguardMission::create([
            'title' => 'Scheduled Mission',
            'description' => 'Mission description',
            'armada_type' => 'both',
            'points_reward' => 20,
            'start_time' => Carbon::now()->addDays(1),
            'end_time' => Carbon::now()->addDays(3),
        ]);

        $response = $this->actingAs($admin)
            ->post(route('vanguard.admin.missions.update', $mission->id), [
                'title' => 'Updated Mission Title',
                'description' => 'Updated description',
                'armada_type' => 'darat_laut',
                'points_reward' => 30,
                'start_time' => Carbon::now()->addDays(2)->toDateTimeString(),
                'end_time' => Carbon::now()->addDays(4)->toDateTimeString(),
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('vanguard_missions', [
            'id' => $mission->id,
            'title' => 'Updated Mission Title',
        ]);
    }

    public function test_admin_cannot_edit_active_mission_due_to_mutability_lock()
    {
        $admin = User::factory()->create(['role' => 'admin_manager']);
        $mission = \App\Modules\Vanguard\Models\VanguardMission::create([
            'title' => 'Active Mission',
            'description' => 'Mission description',
            'armada_type' => 'both',
            'points_reward' => 20,
            'start_time' => Carbon::now()->subHour(),
            'end_time' => Carbon::now()->addHours(5),
        ]);

        $response = $this->actingAs($admin)
            ->post(route('vanguard.admin.missions.update', $mission->id), [
                'title' => 'Attempted Title Update',
                'description' => 'Will fail',
                'armada_type' => 'both',
                'points_reward' => 25,
                'start_time' => Carbon::now()->addDays(2)->toDateTimeString(),
                'end_time' => Carbon::now()->addDays(4)->toDateTimeString(),
            ]);

        $response->assertSessionHasErrors('start_time');
        $this->assertDatabaseHas('vanguard_missions', [
            'id' => $mission->id,
            'title' => 'Active Mission', // unchanged
        ]);
    }
}
