<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
use App\Models\LiquidityPoolSystem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;

class GameController extends Controller
{
    /**
     * Get user's wallet balances and the global liquidity pool state.
     */
    public function getState()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'error' => 'Unauthenticated'], 401);
        }

        $wallet = Wallet::firstOrCreate(
            ['user_id' => $user->id],
            ['silver_balance' => 0, 'gold_balance' => 0]
        );

        $lp = LiquidityPoolSystem::find(1);
        if (!$lp) {
            $lp = LiquidityPoolSystem::create([
                'id' => 1,
                'total_fiat_in_bank' => 500000,
                'total_gold_in_circulation' => 0,
            ]);
        }

        return response()->json([
            'success' => true,
            'wallet' => [
                'silver_balance' => $wallet->silver_balance,
                'gold_balance' => $wallet->gold_balance,
            ],
            'global_pool' => [
                'total_fiat_in_bank' => $lp->total_fiat_in_bank,
                'total_gold_in_circulation' => $lp->total_gold_in_circulation,
            ],
            'conversion_rate' => 100 // 1 Gold = 100 Silver
        ]);
    }

    /**
     * Claim accumulated gold coins, deducting silver coins from user's wallet.
     */
    public function claimGold(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'error' => 'Unauthenticated'], 401);
        }

        $request->validate([
            'gold_amount' => 'required|integer|min:1',
        ]);

        $goldAmount = intval($request->input('gold_amount'));
        $silverCost = $goldAmount * 100; // 1 Gold = 100 Silver conversion rate

        try {
            $result = DB::transaction(function () use ($user, $goldAmount, $silverCost) {
                // 1. Lock and fetch user's wallet
                $wallet = Wallet::where('user_id', $user->id)->lockForUpdate()->first();
                if (!$wallet) {
                    throw new Exception('Dompet Anda tidak ditemukan.');
                }

                // 2. Validate silver balance
                if ($wallet->silver_balance < $silverCost) {
                    throw new Exception("Saldo koin perak tidak cukup untuk klaim. Dibutuhkan: {$silverCost} Perak, dimiliki: {$wallet->silver_balance} Perak.");
                }

                // 3. Lock and fetch liquidity pool
                $lp = LiquidityPoolSystem::where('id', 1)->lockForUpdate()->first();
                if (!$lp) {
                    throw new Exception('Sistem Liquidity Pool tidak terinisialisasi.');
                }

                // 4. Check constraint BEFORE updating the database to avoid SQL check constraint exception
                $projectedGold = $lp->total_gold_in_circulation + $goldAmount;
                if ($projectedGold > $lp->total_fiat_in_bank) {
                    throw new Exception(
                        "Gagal klaim: Transaksi melanggar batas likuiditas global. Koin Emas beredar ({$projectedGold}) melebihi kapasitas fiat ({$lp->total_fiat_in_bank})."
                    );
                }

                // 5. Deduct silver and add gold to wallet
                $wallet->decrement('silver_balance', $silverCost);
                $wallet->increment('gold_balance', $goldAmount);

                // 6. Update liquidity pool circulation
                $lp->increment('total_gold_in_circulation', $goldAmount);

                return [
                    'wallet' => $wallet->fresh(),
                    'global_pool' => $lp->fresh()
                ];
            });

            return response()->json([
                'success' => true,
                'message' => "Berhasil mengklaim {$goldAmount} Koin Emas!",
                'wallet' => [
                    'silver_balance' => $result['wallet']->silver_balance,
                    'gold_balance' => $result['wallet']->gold_balance,
                ],
                'global_pool' => [
                    'total_fiat_in_bank' => $result['global_pool']->total_fiat_in_bank,
                    'total_gold_in_circulation' => $result['global_pool']->total_gold_in_circulation,
                ]
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
