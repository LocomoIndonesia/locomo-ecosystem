<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Wallet;
use App\Models\Quest;
use App\Models\EscrowTransaction;
use App\Models\LiquidityPoolSystem;
use App\Services\WalletService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Exception;

class WalletTransactionTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure Liquidity Pool row exists
        LiquidityPoolSystem::updateOrCreate(
            ['id' => 1],
            [
                'total_fiat_in_bank' => 500000,
                'total_gold_in_circulation' => 0,
            ]
        );
    }

    /**
     * Test Case 1: Starter Kit Purchase (Logic 1)
     */
    public function test_starter_kit_purchase()
    {
        $user = User::create([
            'name' => 'Sutan Uji',
            'whatsapp' => '08999999991',
            'role' => 'GUEST',
        ]);

        $result = WalletService::purchaseStarterKit($user->id);

        $this->assertEquals('MERCHANT', $result['role']);
        $this->assertEquals(100000, $result['wallet']->silver_balance);
        $this->assertEquals(525000, $result['liquidity_pool']->total_fiat_in_bank);

        $dbUser = User::find($user->id);
        $this->assertEquals('MERCHANT', $dbUser->role);
    }

    /**
     * Test Case 2: Staking a Quest (Logic 2)
     */
    public function test_quest_staking()
    {
        $user = User::create([
            'name' => 'Sutan Uji',
            'whatsapp' => '08999999991',
            'role' => 'MERCHANT',
        ]);
        Wallet::create([
            'user_id' => $user->id,
            'silver_balance' => 100000,
            'gold_balance' => 0,
        ]);

        $quest = Quest::create([
            'title' => 'Misi Uji Staking',
            'description' => 'Misi uji coba staking.',
            'stake_amount_silver' => 10000,
            'reward_amount_gold' => 2000,
            'time_limit_hours' => 24,
            'is_active' => true,
        ]);

        $result = WalletService::stakeQuest($user->id, $quest->id);

        $this->assertEquals(90000, $result['wallet']->silver_balance);
        $this->assertEquals('LOCKED', $result['escrow']->status);
        $this->assertEquals(10000, $result['escrow']->staked_silver);
    }

    /**
     * Test Case 3: Quest Success Resolution (Logic 3)
     */
    public function test_quest_resolution_success()
    {
        // Set up referral tree: Datuk -> Sutan -> Buyung
        $datuk = User::create([
            'name' => 'Datuk Grand Uji',
            'whatsapp' => '08999999990',
            'role' => 'GUEST',
        ]);
        Wallet::create(['user_id' => $datuk->id, 'silver_balance' => 0, 'gold_balance' => 0]);

        $sutan = User::create([
            'name' => 'Sutan Upline Uji',
            'whatsapp' => '08999999992',
            'role' => 'GUEST',
            'upline_id' => $datuk->id,
        ]);
        Wallet::create(['user_id' => $sutan->id, 'silver_balance' => 0, 'gold_balance' => 0]);

        $buyung = User::create([
            'name' => 'Buyung Merchant Uji',
            'whatsapp' => '08999999993',
            'role' => 'MERCHANT',
            'upline_id' => $sutan->id,
            'grand_upline_id' => $datuk->id,
        ]);
        Wallet::create(['user_id' => $buyung->id, 'silver_balance' => 90000, 'gold_balance' => 0]);

        $quest = Quest::create([
            'title' => 'Misi Uji Success',
            'description' => 'Misi uji coba sukses.',
            'stake_amount_silver' => 10000,
            'reward_amount_gold' => 2000,
            'time_limit_hours' => 24,
            'is_active' => true,
        ]);

        $escrow = EscrowTransaction::create([
            'user_id' => $buyung->id,
            'quest_id' => $quest->id,
            'staked_silver' => 10000,
            'status' => 'LOCKED',
            'expires_at' => now()->addHours(24),
        ]);

        // Resolve quest as SUCCESS
        $result = WalletService::resolveQuestSuccess($escrow->id);

        // Stake returned minus 5% gas: 10000 - 500 = 9500. New silver balance: 90000 + 9500 = 99500
        $this->assertEquals(99500, $result['user_wallet']->silver_balance);
        $this->assertEquals(2000, $result['user_wallet']->gold_balance);
        
        // Level 1 Commission (25% of 2000 = 500)
        $sutanWallet = Wallet::where('user_id', $sutan->id)->first();
        $this->assertEquals(500, $sutanWallet->gold_balance);

        // Level 2 Commission (10% of 2000 = 200)
        $datukWallet = Wallet::where('user_id', $datuk->id)->first();
        $this->assertEquals(200, $datukWallet->gold_balance);

        // Global circulation increase: 2000 + 500 + 200 = 2700
        $this->assertEquals(2700, $result['liquidity_pool']->total_gold_in_circulation);
        $this->assertEquals('SUCCESS', $result['escrow']->status);
    }

    /**
     * Test Case 4: Quest Resolution Failed (Logic 4)
     */
    public function test_quest_resolution_failed()
    {
        $user = User::create([
            'name' => 'Sutan Uji',
            'whatsapp' => '08999999991',
            'role' => 'MERCHANT',
        ]);
        $wallet = Wallet::create([
            'user_id' => $user->id,
            'silver_balance' => 90000,
            'gold_balance' => 0,
        ]);

        $quest = Quest::create([
            'title' => 'Misi Uji Gagal',
            'description' => 'Misi uji coba gagal.',
            'stake_amount_silver' => 10000,
            'reward_amount_gold' => 2000,
            'time_limit_hours' => 24,
            'is_active' => true,
        ]);

        $escrow = EscrowTransaction::create([
            'user_id' => $user->id,
            'quest_id' => $quest->id,
            'staked_silver' => 10000,
            'status' => 'LOCKED',
            'expires_at' => now()->addHours(24),
        ]);

        $result = WalletService::resolveQuestFailed($escrow->id);

        $wallet->refresh();
        $this->assertEquals(90000, $wallet->silver_balance); // Stake was NOT returned
        $this->assertEquals('FAILED', $result['escrow']->status);
        
        // Lost stake (10,000) converted 1:1 into fiat in the pool
        $this->assertEquals(510000, $result['liquidity_pool']->total_fiat_in_bank);
    }

    /**
     * Test Case 5: Liquidity pool constraint check
     */
    public function test_liquidity_pool_constraint_violation()
    {
        $user = User::create([
            'name' => 'Buyung Merchant Uji',
            'whatsapp' => '08999999993',
            'role' => 'MERCHANT',
        ]);
        Wallet::create(['user_id' => $user->id, 'silver_balance' => 90000, 'gold_balance' => 0]);

        $quest = Quest::create([
            'title' => 'Misi Uji Success',
            'description' => 'Misi uji coba sukses.',
            'stake_amount_silver' => 10000,
            'reward_amount_gold' => 2000,
            'time_limit_hours' => 24,
            'is_active' => true,
        ]);

        $escrow = EscrowTransaction::create([
            'user_id' => $user->id,
            'quest_id' => $quest->id,
            'staked_silver' => 10000,
            'status' => 'LOCKED',
            'expires_at' => now()->addHours(24),
        ]);

        // Manipulate pool reserves to be below the potential reward (e.g. set fiat bank to 1000, reward is 2000)
        $lp = LiquidityPoolSystem::find(1);
        $lp->update(['total_fiat_in_bank' => 1000]);

        // Expect Exception due to CHECK constraint
        try {
            WalletService::resolveQuestSuccess($escrow->id);
            $this->fail('Expected transaction to fail due to liquidity constraints');
        } catch (Exception $e) {
            $hasConstraintError = str_contains($e->getMessage(), 'check_gold_le_fiat') || 
                                  str_contains($e->getMessage(), 'Melanggar batas likuiditas global');
            $this->assertTrue($hasConstraintError, "Exception message did not match expected check constraint: " . $e->getMessage());
        }
    }
}
