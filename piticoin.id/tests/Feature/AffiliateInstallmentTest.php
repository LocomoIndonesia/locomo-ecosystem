<?php

namespace Tests\Feature;

use App\Models\AffiliateCommission;
use App\Models\EcosystemService;
use App\Models\OrderInstallment;
use App\Models\ServiceOrder;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AffiliateInstallmentTest extends TestCase
{
    use DatabaseTransactions;

    public function test_internal_service_onboarding_with_installments_and_commissions(): void
    {
        $unique = rand(10000, 99999);

        // 1. Setup User Hierarchy: L2 -> L1 -> Member
        $uplineL2 = User::create([
            'name' => 'Grand Upline L2',
            'username' => 'upline_l2_' . $unique,
            'whatsapp' => '0812' . $unique . '2',
            'password' => Hash::make('password'),
            'role' => 'USER',
            'onboarding_step' => 6,
        ]);
        Wallet::create(['user_id' => $uplineL2->id, 'gold_balance' => 0, 'silver_balance' => 0]);

        $uplineL1 = User::create([
            'name' => 'Direct Sponsor L1',
            'username' => 'upline_l1_' . $unique,
            'whatsapp' => '0812' . $unique . '1',
            'password' => Hash::make('password'),
            'role' => 'USER',
            'upline_id' => $uplineL2->id,
            'onboarding_step' => 6,
        ]);
        Wallet::create(['user_id' => $uplineL1->id, 'gold_balance' => 0, 'silver_balance' => 0]);

        $member = User::create([
            'name' => 'Calon Jamaah Umroh',
            'username' => 'jamaah_' . $unique,
            'whatsapp' => '0812' . $unique . '3',
            'password' => Hash::make('password'),
            'role' => 'USER',
            'upline_id' => $uplineL1->id,
            'onboarding_step' => 6,
        ]);
        $memberWallet = Wallet::create(['user_id' => $member->id, 'gold_balance' => 15000, 'silver_balance' => 0]);

        // 2. Ensure Umroh Service exists with 3-level commissions
        $service = EcosystemService::firstOrCreate(
            ['code' => 'umroh-barokah'],
            [
                'name' => 'Paket Umroh Barokah & Maqbul 2026',
                'category' => 'UMROH',
                'service_type' => 'INTERNAL',
                'description' => 'Paket Umroh 12 hari hotel bintang 5',
                'price_gold' => 20000,
                'price_fiat' => 25000000,
                'allows_installments' => true,
                'min_down_payment_gold' => 4000,
                'min_down_payment_fiat' => 5000000,
                'commission_rules' => [
                    'depth' => 3,
                    'levels' => [
                        ['level' => 1, 'percentage' => 10], // 10%
                        ['level' => 2, 'percentage' => 5],  // 5%
                        ['level' => 3, 'percentage' => 2.5],
                    ],
                ],
                'status' => 'ACTIVE',
                'is_active' => true,
            ]
        );

        // 3. Act: Member registers with DP of 4,000 Gold
        $this->actingAs($member);

        $response = $this->postJson('/services/' . $service->code . '/submit', [
            'beneficiary_identifier' => $member->whatsapp,
            'payment_method' => 'GOLD',
            'payment_type' => 'INSTALLMENT',
            'down_payment_amount' => 4000,
            'form_data' => [
                'passport_name' => 'MUHAMMAD TEST',
                'gender' => 'Laki-Laki (Ikhwan)',
                'uniform_size' => 'L',
                'room_type' => 'Quad (Sekamar Berempat)',
                'departure_season' => 'Syawal 1447 H / April 2026',
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // 4. Assert Member Wallet was debited 4,000 Gold
        $memberWallet->refresh();
        $this->assertEquals(11000, (float) $memberWallet->gold_balance);

        // 5. Assert Upline L1 received 10% of 4,000 = 400 Gold
        $wL1 = Wallet::where('user_id', $uplineL1->id)->first();
        $this->assertEquals(400, (float) $wL1->gold_balance);

        // 6. Assert Upline L2 received 5% of 4,000 = 200 Gold
        $wL2 = Wallet::where('user_id', $uplineL2->id)->first();
        $this->assertEquals(200, (float) $wL2->gold_balance);

        // 7. Assert ServiceOrder and Commissions
        $order = ServiceOrder::where('user_id', $member->id)->where('service_id', $service->id)->first();
        $this->assertNotNull($order);
        $this->assertEquals(20000, (float) $order->total_amount_gold);
        $this->assertEquals(4000, (float) $order->paid_amount_gold);
        $this->assertEquals(16000, (float) $order->remaining_amount_gold);
        $this->assertEquals('INSTALLMENT', $order->payment_type);

        $commissions = AffiliateCommission::where('service_order_id', $order->id)->get();
        $this->assertCount(2, $commissions); // L1 and L2

        // 8. Act: Pay Second Installment of 6,000 Gold
        $payResponse = $this->postJson('/api/services/orders/' . $order->id . '/pay-installment', [
            'amount' => 6000,
            'payment_method' => 'GOLD',
            'notes' => 'Pembayaran Termin Ke-2',
        ]);

        $payResponse->assertStatus(200);
        $payResponse->assertJson(['success' => true]);

        // 9. Assert Member Wallet debited another 6,000 Gold (balance was 11000 -> 5000)
        $memberWallet->refresh();
        $this->assertEquals(5000, (float) $memberWallet->gold_balance);

        // 10. Assert Upline L1 received another 10% of 6,000 = 600 Gold (total 1000)
        $wL1->refresh();
        $this->assertEquals(1000, (float) $wL1->gold_balance);

        // 11. Assert Upline L2 received another 5% of 6,000 = 300 Gold (total 500)
        $wL2->refresh();
        $this->assertEquals(500, (float) $wL2->gold_balance);

        // 12. Assert Order Updated: paid 10000, remaining 10000
        $order->refresh();
        $this->assertEquals(10000, (float) $order->paid_amount_gold);
        $this->assertEquals(10000, (float) $order->remaining_amount_gold);
        $this->assertEquals(2, $order->installments()->count());
    }
}
