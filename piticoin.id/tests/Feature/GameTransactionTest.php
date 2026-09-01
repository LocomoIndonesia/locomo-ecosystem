<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Wallet;
use App\Models\LiquidityPoolSystem;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class GameTransactionTest extends TestCase
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
     * Test retrieving game state successfully.
     */
    public function test_get_game_state_authenticated()
    {
        $user = User::create([
            'name' => 'Warga Game Uji',
            'whatsapp' => '08999999999',
            'role' => 'MERCHANT',
            'onboarding_step' => 6,
            'license_status' => 'ACTIVE',
        ]);

        Wallet::create([
            'user_id' => $user->id,
            'silver_balance' => 5000,
            'gold_balance' => 10,
        ]);

        $response = $this->actingAs($user)->getJson(route('game.harta-karun.state'));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'wallet' => [
                    'silver_balance' => 5000,
                    'gold_balance' => 10,
                ],
                'global_pool' => [
                    'total_fiat_in_bank' => 500000,
                    'total_gold_in_circulation' => 0,
                ],
                'conversion_rate' => 100
            ]);
    }

    /**
     * Test claiming gold successfully.
     */
    public function test_claim_gold_success()
    {
        $user = User::create([
            'name' => 'Warga Game Uji',
            'whatsapp' => '08999999999',
            'role' => 'MERCHANT',
            'onboarding_step' => 6,
            'license_status' => 'ACTIVE',
        ]);

        $wallet = Wallet::create([
            'user_id' => $user->id,
            'silver_balance' => 1000,
            'gold_balance' => 5,
        ]);

        // Claim 5 Gold coins. Costs 5 * 100 = 500 Silver.
        $response = $this->actingAs($user)->postJson(route('game.harta-karun.claim'), [
            'gold_amount' => 5
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'wallet' => [
                    'silver_balance' => 500, // 1000 - 500
                    'gold_balance' => 10,    // 5 + 5
                ]
            ]);

        $this->assertEquals(500, $wallet->fresh()->silver_balance);
        $this->assertEquals(10, $wallet->fresh()->gold_balance);

        $lp = LiquidityPoolSystem::find(1);
        $this->assertEquals(5, $lp->total_gold_in_circulation);
    }

    /**
     * Test claiming gold fails due to insufficient silver balance.
     */
    public function test_claim_gold_insufficient_silver()
    {
        $user = User::create([
            'name' => 'Warga Game Uji',
            'whatsapp' => '08999999999',
            'role' => 'MERCHANT',
            'onboarding_step' => 6,
            'license_status' => 'ACTIVE',
        ]);

        Wallet::create([
            'user_id' => $user->id,
            'silver_balance' => 100, // Only 100 silver
            'gold_balance' => 5,
        ]);

        // Claim 2 Gold coins. Costs 2 * 100 = 200 Silver.
        $response = $this->actingAs($user)->postJson(route('game.harta-karun.claim'), [
            'gold_amount' => 2
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => 'Saldo koin perak tidak cukup untuk klaim. Dibutuhkan: 200 Perak, dimiliki: 100 Perak.'
            ]);
    }

    /**
     * Test claiming gold fails if it violates liquidity pool capacity.
     */
    public function test_claim_gold_exceeds_liquidity_capacity()
    {
        // Update pool capacity to be low
        $lp = LiquidityPoolSystem::find(1);
        $lp->update([
            'total_fiat_in_bank' => 200,
            'total_gold_in_circulation' => 150,
        ]);

        $user = User::create([
            'name' => 'Warga Game Uji',
            'whatsapp' => '08999999999',
            'role' => 'MERCHANT',
            'onboarding_step' => 6,
            'license_status' => 'ACTIVE',
        ]);

        Wallet::create([
            'user_id' => $user->id,
            'silver_balance' => 10000,
            'gold_balance' => 0,
        ]);

        // Claim 100 Gold. Costs 10,000 Silver.
        // Would push circulation to 150 + 100 = 250, exceeding 200 bank capacity.
        $response = $this->actingAs($user)->postJson(route('game.harta-karun.claim'), [
            'gold_amount' => 100
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => 'Gagal klaim: Transaksi melanggar batas likuiditas global. Koin Emas beredar (250) melebihi kapasitas fiat (200).'
            ]);
    }
}
