<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Wallet;
use App\Models\LiquidityPoolSystem;
use App\Services\ExperienceService;
use App\Services\WalletService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Exception;

class CharacterExperienceTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        LiquidityPoolSystem::updateOrCreate(
            ['id' => 1],
            [
                'total_fiat_in_bank' => 500000,
                'total_gold_in_circulation' => 0,
            ]
        );
    }

    /**
     * Test adding experience points and simple level up.
     */
    public function test_add_xp_trigger_level_up()
    {
        $user = User::create([
            'name' => 'Pendekar Uji',
            'whatsapp' => '08777777772',
            'role' => 'MERCHANT',
            'level' => 1,
            'experience_points' => 900
        ]);

        // Add 150 XP. Level 1 needs 1000 XP.
        // Final state: Level 2, 50 XP
        $result = ExperienceService::addExperience($user->id, 150, 'QUEST_RESOLVED', 'Menyelesaikan Misi Singgalang');

        $this->assertEquals(2, $result['level']);
        $this->assertEquals(50, $result['experience_points']);

        $user->refresh();
        $this->assertEquals(2, $user->level);
        $this->assertEquals(50, $user->experience_points);

        $this->assertDatabaseHas('experience_logs', [
            'user_id' => $user->id,
            'xp_amount' => 150,
            'source' => 'QUEST_RESOLVED',
            'description' => 'Menyelesaikan Misi Singgalang'
        ]);
    }

    /**
     * Test transfer constraint (blocked for users with less than 5 active downlines).
     */
    public function test_transfer_blocked_less_than_five_downlines()
    {
        $sender = User::create([
            'name' => 'Saudagar Pengirim',
            'whatsapp' => '08777777773',
            'role' => 'MERCHANT',
            'license_status' => 'ACTIVE',
        ]);

        Wallet::create([
            'user_id' => $sender->id,
            'silver_balance' => 10000,
            'gold_balance' => 0
        ]);

        $receiver = User::create([
            'name' => 'Saudagar Penerima',
            'whatsapp' => '08777777774',
            'role' => 'MERCHANT',
            'license_status' => 'ACTIVE',
        ]);

        // Try to transfer. Upline has 0 downlines, should throw exception.
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Akses Transfer Terkunci: Butuh minimal 5 downline aktif untuk membuka fitur ini.');

        WalletService::transferCoins($sender->id, $receiver->whatsapp, 1000, 'silver');
    }

    /**
     * Test transfer success (allowed when user has 5 active downlines).
     */
    public function test_transfer_success_with_five_downlines()
    {
        $sender = User::create([
            'name' => 'Saudagar Pengirim',
            'whatsapp' => '08777777773',
            'role' => 'MERCHANT',
            'license_status' => 'ACTIVE',
        ]);

        Wallet::create([
            'user_id' => $sender->id,
            'silver_balance' => 10000,
            'gold_balance' => 0
        ]);

        // Create 5 active downlines
        for ($i = 0; $i < 5; $i++) {
            User::create([
                'name' => "Downline {$i}",
                'whatsapp' => "0877777700{$i}",
                'role' => 'MERCHANT',
                'upline_id' => $sender->id,
                'license_status' => 'ACTIVE'
            ]);
        }

        $receiver = User::create([
            'name' => 'Saudagar Penerima',
            'whatsapp' => '08777777774',
            'role' => 'MERCHANT',
            'license_status' => 'ACTIVE',
        ]);
        $receiverWallet = Wallet::create([
            'user_id' => $receiver->id,
            'silver_balance' => 0,
            'gold_balance' => 0
        ]);

        // Transfer 1000 Silver coins
        $result = WalletService::transferCoins($sender->id, $receiver->whatsapp, 1000, 'silver');

        $this->assertEquals(9000, $result['sender_wallet']->silver_balance);
        $this->assertEquals(1000, $result['receiver_wallet']->silver_balance);
    }
}
