<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Wallet;
use App\Models\LiquidityPoolSystem;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class OnboardingTest extends TestCase
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
     * Test step redirection for a newly registered user (step 1 -> step 2 redirects to awakening).
     */
    public function test_new_user_redirected_to_awakening()
    {
        $user = User::create([
            'name' => 'Calon Saudagar',
            'whatsapp' => '08777777771',
            'role' => 'GUEST',
            'onboarding_step' => 2 // Registered and awakening next
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertRedirect(route('onboarding.awakening'));
    }

    /**
     * Test step saving awakening (faction selection).
     */
    public function test_save_awakening_success()
    {
        $user = User::create([
            'name' => 'Calon Saudagar',
            'whatsapp' => '08777777771',
            'role' => 'GUEST',
            'onboarding_step' => 2
        ]);

        $response = $this->actingAs($user)->post('/onboarding/awakening', [
            'faction' => 'NISNAS'
        ]);

        $response->assertRedirect(route('onboarding.path'));
        
        $user->refresh();
        $this->assertEquals('NISNAS', $user->faction);
        $this->assertEquals(3, $user->onboarding_step);
        
        // Assert experience points added
        $this->assertEquals(100, $user->experience_points);
        $this->assertDatabaseHas('experience_logs', [
            'user_id' => $user->id,
            'xp_amount' => 100,
            'source' => 'ONBOARDING'
        ]);
    }

    /**
     * Test saving license (pledge) successfully.
     */
    public function test_save_pledge_saudagar_license()
    {
        $user = User::create([
            'name' => 'Calon Saudagar',
            'whatsapp' => '08777777771',
            'role' => 'GUEST',
            'faction' => 'NISNAS',
            'role_class' => 'PENDEKAR',
            'onboarding_step' => 5
        ]);

        $response = $this->actingAs($user)->post('/onboarding/pledge', [
            'license_type' => 'SAUDAGAR'
        ]);

        $response->assertRedirect(route('dashboard'));

        $user->refresh();
        $this->assertEquals('SAUDAGAR', $user->license_type);
        $this->assertEquals('ACTIVE', $user->license_status);
        $this->assertEquals('MERCHANT', $user->role);
        $this->assertEquals(6, $user->onboarding_step);

        // Wallet perak incremented
        $wallet = Wallet::where('user_id', $user->id)->first();
        $this->assertNotNull($wallet);
        $this->assertEquals(200000, $wallet->silver_balance);

        // Liquidity pool incremented by 25% of Rp 200,000 (which is 50,000)
        $lp = LiquidityPoolSystem::find(1);
        $this->assertEquals(550000, $lp->total_fiat_in_bank);
    }
}
