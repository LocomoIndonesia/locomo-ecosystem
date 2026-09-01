<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Wallet;
use App\Models\LiquidityPoolSystem;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class KoaGameTransactionTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure Liquidity Pool row exists with 500k gold fiat equivalent
        LiquidityPoolSystem::updateOrCreate(
            ['id' => 1],
            [
                'total_fiat_in_bank' => 500000,
                'total_gold_in_circulation' => 0,
            ]
        );
    }

    /**
     * Test state retrieval when user has active license.
     */
    public function test_get_koa_state_active_member()
    {
        $user = User::create([
            'name' => 'Koa Player Active',
            'whatsapp' => '08999999991',
            'role' => 'MERCHANT',
            'onboarding_step' => 6,
            'license_status' => 'ACTIVE'
        ]);

        Wallet::create([
            'user_id' => $user->id,
            'silver_balance' => 20000,
            'gold_balance' => 50
        ]);

        $response = $this->actingAs($user)->getJson(route('game.koa.state'));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'wallet' => [
                    'silver_balance' => 20000,
                    'gold_balance' => 50,
                ],
                'global_pool' => [
                    'total_fiat_in_bank' => 500000,
                    'total_gold_in_circulation' => 0
                ]
            ]);
    }

    /**
     * Test state retrieval is blocked when user license is inactive.
     */
    public function test_get_koa_state_blocked_inactive_member()
    {
        $user = User::create([
            'name' => 'Koa Player Inactive',
            'whatsapp' => '08999999992',
            'role' => 'MERCHANT',
            'onboarding_step' => 6,
            'license_status' => 'INACTIVE'
        ]);

        Wallet::create([
            'user_id' => $user->id,
            'silver_balance' => 20000,
            'gold_balance' => 50
        ]);

        $response = $this->actingAs($user)->getJson(route('game.koa.state'));

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'error' => 'Akses ditolak: Hanya untuk member aktif dengan lisensi.'
            ]);
    }

    /**
     * Test secure spin reduces silver balance and outputs cascading structures.
     */
    public function test_koa_spin_success()
    {
        $user = User::create([
            'name' => 'Koa Player Active',
            'whatsapp' => '08999999991',
            'role' => 'MERCHANT',
            'onboarding_step' => 6,
            'license_status' => 'ACTIVE'
        ]);

        Wallet::create([
            'user_id' => $user->id,
            'silver_balance' => 50000, // 50,000 Silver
            'gold_balance' => 0
        ]);

        // Total bet 18,000 Silver
        $response = $this->actingAs($user)->postJson(route('game.koa.spin'), [
            'bet_amount' => 18000,
            'free_spins_active' => false,
            'free_spins_left' => 0,
            'free_spins_win_accumulated' => 0,
            'user_gold_quota' => 0
        ]);

        $response->assertStatus(200);
        $data = $response->json();

        $this->assertTrue($data['success']);
        // Silver should be: starting (50,000) - bet (18,000) + win
        $expectedSilver = 50000 - 18000 + $data['silverWin'];
        $this->assertEquals($expectedSilver, $data['userSilver']);
        $this->assertCount(5*5, $data['grids'][0]); // First grid is 25 items
    }

    /**
     * Test spin fails if bet amount is not valid option.
     */
    public function test_koa_spin_invalid_bet_amount()
    {
        $user = User::create([
            'name' => 'Koa Player Active',
            'whatsapp' => '08999999991',
            'role' => 'MERCHANT',
            'onboarding_step' => 6,
            'license_status' => 'ACTIVE'
        ]);

        Wallet::create([
            'user_id' => $user->id,
            'silver_balance' => 50000,
            'gold_balance' => 0
        ]);

        // 777 is not in bet options
        $response = $this->actingAs($user)->postJson(route('game.koa.spin'), [
            'bet_amount' => 777,
            'free_spins_active' => false,
            'free_spins_left' => 0,
            'free_spins_win_accumulated' => 0,
            'user_gold_quota' => 0
        ]);

        $response->assertStatus(400); // Validation error
    }

    /**
     * Test claim gold success burns silver and adds gold vault balance.
     */
    public function test_koa_claim_gold_success()
    {
        $user = User::create([
            'name' => 'Koa Player Active',
            'whatsapp' => '08999999991',
            'role' => 'MERCHANT',
            'onboarding_step' => 6,
            'license_status' => 'ACTIVE'
        ]);

        Wallet::create([
            'user_id' => $user->id,
            'silver_balance' => 5000, // 5000 silver
            'gold_balance' => 10
        ]);

        // Claim 20 Gold. Costs 20 * 100 = 2000 Silver.
        $response = $this->actingAs($user)->postJson(route('game.koa.claim'), [
            'gold_amount' => 20
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'wallet' => [
                    'silver_balance' => 3000, // 5000 - 2000
                    'gold_balance' => 30      // 10 + 20
                ]
            ]);

        $lp = LiquidityPoolSystem::find(1);
        $this->assertEquals(20, $lp->total_gold_in_circulation);
    }

    /**
     * Test claim gold fails if wallet does not have enough silver to burn.
     */
    public function test_koa_claim_gold_insufficient_silver()
    {
        $user = User::create([
            'name' => 'Koa Player Active',
            'whatsapp' => '08999999991',
            'role' => 'MERCHANT',
            'onboarding_step' => 6,
            'license_status' => 'ACTIVE'
        ]);

        Wallet::create([
            'user_id' => $user->id,
            'silver_balance' => 500, // Only 500 silver
            'gold_balance' => 10
        ]);

        // Claim 10 Gold. Costs 10 * 100 = 1000 Silver.
        $response = $this->actingAs($user)->postJson(route('game.koa.claim'), [
            'gold_amount' => 10
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => 'Saldo koin perak tidak cukup untuk klaim. Dibutuhkan: 1000 Perak, dimiliki: 500 Perak.'
            ]);
    }
}
