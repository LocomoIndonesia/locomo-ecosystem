<?php

namespace Tests\Feature;

use App\Models\MissionVerification;
use App\Models\MissionVerificationJuror;
use App\Models\User;
use App\Models\Wallet;
use App\Services\MissionVerificationService;
use App\Services\ReputationService;
use App\Services\WebPushService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class MissionVerificationTest extends TestCase
{
    use DatabaseTransactions;

    protected function createTestUser(string $name = 'User', int $silver = 500, int $repScore = 300): User
    {
        $user = User::create([
            'name' => $name . '_' . uniqid(),
            'username' => 'test_' . uniqid(),
            'whatsapp' => '08' . rand(100000000, 999999999),
            'role' => 'CONQUEROR',
            'license_status' => 'ACTIVE',
            'onboarding_step' => 6,
            'experience_points' => 100,
        ]);

        Wallet::create([
            'user_id' => $user->id,
            'silver_balance' => $silver,
            'gold_balance' => 10,
        ]);

        $user->reputation()->create([
            'base_score' => $repScore,
            'total_score' => $repScore,
            'tier' => 'WARGA_TELADAN',
            'updated_at' => now(),
        ]);

        return $user;
    }

    public function test_submit_mission_proof_automatically_assigns_5_independent_jurors(): void
    {
        // Setup 6 eligible users so 5 can be selected
        $submitter = $this->createTestUser('Submitter');
        $upline = $this->createTestUser('Upline');
        $submitter->update(['upline_id' => $upline->id]);

        $candidates = [];
        for ($i = 0; $i < 6; $i++) {
            $candidates[] = $this->createTestUser("Candidate{$i}");
        }

        $service = app(MissionVerificationService::class);
        $verification = $service->submitProof($submitter, [
            'mission_title' => 'Eksplorasi Lembah Harau',
            'proof_description' => 'Berhasil menyelesaikan transaksi pasar fisik di Harau.',
            'reward_gold' => 10,
            'reward_silver' => 100,
        ]);

        $this->assertDatabaseHas('mission_verifications', [
            'id' => $verification->id,
            'submitter_user_id' => $submitter->id,
            'status' => 'IN_VOTING',
        ]);

        $assignedJurors = MissionVerificationJuror::where('verification_id', $verification->id)->get();
        $this->assertCount(5, $assignedJurors);

        // Submitter & Upline must NOT be in the jurors list (anti-collusion)
        $jurorUserIds = $assignedJurors->pluck('juror_user_id')->toArray();
        $this->assertNotContains($submitter->id, $jurorUserIds);
        $this->assertNotContains($upline->id, $jurorUserIds);
    }

    public function test_juror_can_vote_valid_with_stake_and_triggers_consensus_on_majority(): void
    {
        $submitter = $this->createTestUser('Submitter');
        $jurors = [];
        for ($i = 0; $i < 5; $i++) {
            $jurors[] = $this->createTestUser("Juror{$i}", 200);
        }

        $service = app(MissionVerificationService::class);
        $verification = $service->submitProof($submitter, [
            'mission_title' => 'Misi Uji Coba Konsensus',
            'proof_description' => 'Bukti transaksi valid.',
            'reward_gold' => 20,
            'reward_silver' => 200,
        ]);

        // Manually ensure the 5 jurors are our test jurors
        MissionVerificationJuror::where('verification_id', $verification->id)->delete();
        foreach ($jurors as $j) {
            MissionVerificationJuror::create([
                'verification_id' => $verification->id,
                'juror_user_id' => $j->id,
                'vote' => 'PENDING',
                'stake_amount_silver' => 50,
                'reward_amount_silver' => 0,
                'is_slashed' => false,
            ]);
        }

        // Juror 0 votes VALID (1st vote)
        $result1 = $service->castVote($jurors[0], $verification, 'VALID', 'Bukti sangat jelas');
        $this->assertFalse($result1['consensus_reached']);
        $this->assertEquals(150, $jurors[0]->wallet->fresh()->silver_balance); // 200 - 50 stake

        // Juror 1 votes VALID (2nd vote)
        $result2 = $service->castVote($jurors[1], $verification, 'VALID');
        $this->assertFalse($result2['consensus_reached']);

        // Juror 2 votes REJECT (1 reject)
        $result3 = $service->castVote($jurors[2], $verification, 'REJECT');
        $this->assertFalse($result3['consensus_reached']);

        // Juror 3 votes VALID (3rd valid vote -> 3/5 majority reached!)
        $result4 = $service->castVote($jurors[3], $verification, 'VALID');
        $this->assertTrue($result4['consensus_reached']);
        $this->assertEquals('CONSENSUS_APPROVED', $result4['new_status']);

        $verification->refresh();
        $this->assertEquals('CONSENSUS_APPROVED', $verification->status);

        // Winning jurors (0, 1, 3) receive refund (50) + dividend (25) = +75 to their balance
        // Juror 0 started with 200 -> decremented to 150 on vote -> incremented by 75 on consensus = 225
        $this->assertEquals(225, $jurors[0]->wallet->fresh()->silver_balance);
        $this->assertEquals(115, $jurors[0]->fresh()->experience_points); // +15 XP

        // Losing juror (2) voted REJECT -> stake slashed
        $juror2Assignment = MissionVerificationJuror::where('verification_id', $verification->id)
            ->where('juror_user_id', $jurors[2]->id)
            ->first();
        $this->assertTrue($juror2Assignment->is_slashed);
        $this->assertEquals(150, $jurors[2]->wallet->fresh()->silver_balance); // Stake was NOT returned
    }

    public function test_honeypot_trap_slashes_fraudulent_or_careless_juror_and_bans_for_7_days(): void
    {
        $juror = $this->createTestUser('CarelessJuror', 100, 300);

        $service = app(MissionVerificationService::class);
        $honeypot = $service->createHoneypot(
            'Misi Fiktif Belanja Palsu',
            'Ini adalah bukti rekayasa Photoshop untuk menguji kejujuran juri.',
            null
        );

        $assignment = MissionVerificationJuror::create([
            'verification_id' => $honeypot->id,
            'juror_user_id' => $juror->id,
            'vote' => 'PENDING',
            'stake_amount_silver' => 50,
            'reward_amount_silver' => 0,
            'is_slashed' => false,
        ]);

        // Careless juror votes VALID on the honeypot trap!
        $result = $service->castVote($juror, $honeypot, 'VALID', 'Semua terlihat bagus');

        $this->assertTrue($result['fell_for_honeypot']);

        $juror->refresh();
        $assignment->refresh();

        // 1. Slashed
        $this->assertTrue($assignment->is_slashed);

        // 2. 7-day jury ban
        $this->assertTrue($juror->isJuryBanned());
        $this->assertNotNull($juror->jury_banned_until);

        // 3. Reputation reduced by 100 points (from 300 to 200)
        $this->assertEquals(200, $juror->reputation->fresh()->total_score);
    }

    public function test_masked_submitter_attribute_hides_identity_for_blind_validation(): void
    {
        $submitter = $this->createTestUser('Budi Santoso');
        $submitter->update(['name' => 'Budi Santoso']);

        $verification = MissionVerification::create([
            'submitter_user_id' => $submitter->id,
            'mission_title' => 'Misi Rahasia',
            'proof_description' => 'Uji coba masking.',
            'reward_gold' => 10,
            'reward_silver' => 100,
            'status' => 'IN_VOTING',
        ]);

        $masked = $verification->masked_submitter;
        $this->assertNotEquals('Budi Santoso', $masked);
        $this->assertStringContainsString('*', $masked);
    }

    public function test_guest_cannot_access_verifications_index_redirects_to_login(): void
    {
        $response = $this->get('/verifications');
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_access_verifications_index(): void
    {
        $user = $this->createTestUser('AuthUser');
        $response = $this->actingAs($user)->get('/verifications');
        $response->assertStatus(200);
    }

    public function test_juror_can_vote_via_http_post_route(): void
    {
        $submitter = $this->createTestUser('Submitter2');
        $juror = $this->createTestUser('JurorVoter', 100);

        $verification = MissionVerification::create([
            'submitter_user_id' => $submitter->id,
            'mission_title' => 'Misi Uji HTTP',
            'proof_description' => 'Bukti HTTP test.',
            'status' => 'IN_VOTING',
            'valid_votes_count' => 0,
            'reject_votes_count' => 0,
            'total_jurors_count' => 5,
        ]);

        MissionVerificationJuror::create([
            'verification_id' => $verification->id,
            'juror_user_id' => $juror->id,
            'vote' => 'PENDING',
            'stake_amount_silver' => 50,
            'reward_amount_silver' => 0,
            'is_slashed' => false,
        ]);

        $response = $this->actingAs($juror)->post("/verifications/{$verification->id}/vote", [
            'vote' => 'VALID',
            'notes' => 'Bukti sudah sesuai dengan kriteria.',
        ]);

        $response->assertRedirect("/verifications/{$verification->id}");
        $this->assertEquals(50, $juror->wallet->fresh()->silver_balance);
        $this->assertDatabaseHas('mission_verification_jurors', [
            'verification_id' => $verification->id,
            'juror_user_id' => $juror->id,
            'vote' => 'VALID',
        ]);
    }

    public function test_banned_juror_cannot_vote(): void
    {
        $submitter = $this->createTestUser('Submitter3');
        $juror = $this->createTestUser('BannedJuror', 100);
        $juror->update(['jury_banned_until' => now()->addDays(5)]);

        $verification = MissionVerification::create([
            'submitter_user_id' => $submitter->id,
            'mission_title' => 'Misi Banned Test',
            'proof_description' => 'Deskripsi.',
            'status' => 'IN_VOTING',
        ]);

        MissionVerificationJuror::create([
            'verification_id' => $verification->id,
            'juror_user_id' => $juror->id,
            'vote' => 'PENDING',
            'stake_amount_silver' => 50,
        ]);

        $response = $this->actingAs($juror)->post("/verifications/{$verification->id}/vote", [
            'vote' => 'VALID',
        ]);

        $response->assertSessionHas('error');
        $this->assertEquals(100, $juror->wallet->fresh()->silver_balance); // Not deducted
    }
}
