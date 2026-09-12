<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Wallet;
use App\Models\Quest;
use App\Models\EscrowTransaction;
use App\Models\LiquidityPoolSystem;
use App\Services\QuestEvaluatorService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class QuestEvaluatorAndClaimTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure global pool exists
        LiquidityPoolSystem::firstOrCreate(
            ['id' => 1],
            [
                'total_fiat_in_bank' => 10000000,
                'total_gold_in_circulation' => 10000,
                'total_silver_in_circulation' => 50000,
            ]
        );
    }

    public function test_admin_can_create_quest_with_system_parameters()
    {
        $admin = User::create([
            'name' => 'Root Admin',
            'username' => 'admin_test_' . uniqid(),
            'whatsapp' => '08000000099',
            'role' => 'ADMIN',
            'license_status' => 'ACTIVE',
        ]);

        $response = $this->actingAs($admin)->postJson('/admin/quests', [
            'title' => 'Misi Rekrut 2 Karavan',
            'description' => 'Ajak 2 rekan saudagar baru bergabung.',
            'quest_category' => 'DEFAULT',
            'time_window' => 'ALL',
            'target_persona' => 'ALL',
            'stake_amount_silver' => 100,
            'reward_amount_gold' => 40,
            'time_limit_hours' => 24,
            'min_level' => 1,
            'is_active' => true,
            'parameters' => [
                'action_type' => QuestEvaluatorService::ACTION_RECRUIT_MEMBERS,
                'target_threshold' => 2,
            ],
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'quest' => [
                'title' => 'Misi Rekrut 2 Karavan',
                'parameters' => [
                    'action_type' => QuestEvaluatorService::ACTION_RECRUIT_MEMBERS,
                    'target_threshold' => 2,
                ],
            ],
        ]);

        $this->assertDatabaseHas('quests', [
            'title' => 'Misi Rekrut 2 Karavan',
        ]);
    }

    public function test_user_can_track_progress_and_claim_reward_when_target_reached()
    {
        // 1. Setup User and Wallet
        $user = User::create([
            'name' => 'Saudagar Traveler',
            'username' => 'traveler_' . uniqid(),
            'whatsapp' => '08123456780',
            'role' => 'SAUDAGAR',
            'license_status' => 'ACTIVE',
            'onboarding_step' => 6,
        ]);

        $wallet = Wallet::create([
            'user_id' => $user->id,
            'silver_balance' => 500,
            'gold_balance' => 0,
        ]);

        // 2. Setup Quest with RECRUIT_MEMBERS parameter
        $quest = Quest::create([
            'title' => 'Misi Rekrut 2 Saudagar',
            'description' => 'Ajak 2 anggota untuk membuka peti emas.',
            'quest_category' => 'DEFAULT',
            'stake_amount_silver' => 100,
            'reward_amount_gold' => 50,
            'time_limit_hours' => 24,
            'min_level' => 1,
            'is_active' => true,
            'parameters' => [
                'action_type' => QuestEvaluatorService::ACTION_RECRUIT_MEMBERS,
                'target_threshold' => 2,
            ],
        ]);

        // 3. User takes quest (Stake)
        $stakeResponse = $this->actingAs($user)->postJson('/api/quests/stake', [
            'quest_id' => $quest->id,
        ]);
        $stakeResponse->assertOk();

        $escrow = EscrowTransaction::where('user_id', $user->id)
            ->where('quest_id', $quest->id)
            ->first();
        $this->assertNotNull($escrow);
        $this->assertEquals('LOCKED', $escrow->status);
        $this->assertEquals(400, $wallet->fresh()->silver_balance);

        // 4. Check progress via API (Expect 0/2)
        $progressRes = $this->actingAs($user)->getJson("/api/quests/progress/{$escrow->id}");
        $progressRes->assertOk();
        $this->assertEquals(0, $progressRes->json('evaluation.current_progress'));
        $this->assertEquals(2, $progressRes->json('evaluation.target_threshold'));
        $this->assertFalse($progressRes->json('evaluation.is_completed'));

        // 5. Try to claim before fulfilling target (Expect failure)
        $claimFailRes = $this->actingAs($user)->postJson('/api/quests/claim', [
            'escrow_id' => $escrow->id,
        ]);
        $claimFailRes->assertStatus(422);
        $this->assertFalse($claimFailRes->json('success'));

        // 6. User recruits 1st downline
        User::create([
            'name' => 'Recruit One',
            'username' => 'recruit1_' . uniqid(),
            'whatsapp' => '08129990001',
            'upline_id' => $user->id,
            'created_at' => Carbon::now()->addMinute(),
        ]);

        // Progress should now be 1/2
        $eval1 = QuestEvaluatorService::evaluateProgress($escrow->fresh());
        $this->assertEquals(1, $eval1['current_progress']);
        $this->assertFalse($eval1['is_completed']);

        // 7. User recruits 2nd downline
        User::create([
            'name' => 'Recruit Two',
            'username' => 'recruit2_' . uniqid(),
            'whatsapp' => '08129990002',
            'upline_id' => $user->id,
            'created_at' => Carbon::now()->addMinutes(2),
        ]);

        // Progress should now be 2/2 and completed
        $eval2 = QuestEvaluatorService::evaluateProgress($escrow->fresh());
        $this->assertEquals(2, $eval2['current_progress']);
        $this->assertTrue($eval2['is_completed']);

        // 8. User Claims Reward Successfully
        $claimSuccessRes = $this->actingAs($user)->postJson('/api/quests/claim', [
            'escrow_id' => $escrow->id,
        ]);
        $claimSuccessRes->assertOk();
        $this->assertTrue($claimSuccessRes->json('success'));
        $this->assertEquals('SUCCESS', $claimSuccessRes->json('data.status'));

        // Verify Escrow and Wallet changes
        $this->assertEquals('SUCCESS', $escrow->fresh()->status);
        
        // Stake refund: 100 - (5% gas fee = 5) = 95 refund
        // Silver: 400 + 95 = 495
        $this->assertEquals(495, $wallet->fresh()->silver_balance);

        // Gold reward: 0 + 50 = 50
        $this->assertEquals(50, $wallet->fresh()->gold_balance);
    }
}
