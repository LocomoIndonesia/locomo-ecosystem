<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Wallet;
use App\Models\LiquidityPoolSystem;
use App\Services\ExperienceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Exception;

class DigitalGoodsController extends Controller
{
    /**
     * Display the digital goods (Lebur Koin) page.
     */
    public function index()
    {
        // Simple catalog for PPOB products
        $catalog = [
            [
                'id' => '1',
                'name' => 'Pulsa Telkomsel Rp 10.000',
                'cost_gold' => 10,
                'provider' => 'Telkomsel',
            ],
            [
                'id' => '2',
                'name' => 'Pulsa Indosat Rp 25.000',
                'cost_gold' => 25,
                'provider' => 'Indosat',
            ],
            [
                'id' => '3',
                'name' => 'Token Listrik PLN Rp 50.000',
                'cost_gold' => 50,
                'provider' => 'PLN',
            ]
        ];

        return Inertia::render('Games/PersediaanDigital', [
            'user' => Auth::user(),
            'wallet' => Auth::user()->wallet,
            'catalog' => $catalog
        ]);
    }

    /**
     * Redeem/Burn gold coins to purchase a digital product.
     */
    public function redeem(Request $request)
    {
        $request->validate([
            'product_name' => 'required|string',
            'cost_gold' => 'required|integer|min:1',
            'phone_number' => 'required|string|min:9',
        ]);

        $user = Auth::user();
        $costGold = intval($request->input('cost_gold'));
        $productName = $request->input('product_name');
        $phoneNumber = $request->input('phone_number');

        try {
            DB::transaction(function () use ($user, $costGold, $productName, $phoneNumber) {
                // 1. Fetch user's wallet (lock for update)
                $wallet = Wallet::where('user_id', $user->id)->lockForUpdate()->first();
                if (!$wallet || $wallet->gold_balance < $costGold) {
                    throw new Exception("Saldo koin emas tidak cukup. Dibutuhkan: {$costGold} Emas.");
                }

                // 2. Fetch liquidity pool (lock for update)
                $lp = LiquidityPoolSystem::where('id', 1)->lockForUpdate()->first();
                if (!$lp) {
                    throw new Exception("Sistem Liquidity Pool tidak terinisialisasi.");
                }

                // 3. Deduct gold balance (Burn)
                $wallet->decrement('gold_balance', $costGold);

                // 4. Update pool: total gold in circulation goes down by the burned amount
                $lp->decrement('total_gold_in_circulation', $costGold);

                // 5. Award experience points for digital goods trade activity
                ExperienceService::addExperience(
                    $user->id,
                    $costGold * 10, // give 10 XP per Gold coin burned
                    'TRADE',
                    "Lebur Koin Emas untuk membeli: {$productName} ke nomor {$phoneNumber}"
                );
            });

            return response()->json([
                'success' => true,
                'message' => "Lebur koin berhasil! Koin emas Anda telah didebet. Pembelian {$productName} sedang diproses."
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
