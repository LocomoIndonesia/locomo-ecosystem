<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class PosController extends Controller
{
    /**
     * Get business POS summary for the logged in user.
     */
    public function getSummary(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $today = Carbon::now('Asia/Jakarta')->toDateString();
        $cacheKey = "pos_summary_{$user->id}_{$today}";

        $summary = Cache::get($cacheKey, [
            'total_sales' => 450000,
            'transaction_count' => 12,
            'gross_profit' => 135000,
            'is_closed' => false,
            'recent_transactions' => [
                [
                    'id' => 'TRX-' . rand(1000, 9999),
                    'time' => '11:42 WIB',
                    'item' => 'Kopi Susu Gula Aren Minang',
                    'qty' => 2,
                    'amount' => 36000,
                    'method' => 'QRIS',
                ],
                [
                    'id' => 'TRX-' . rand(1000, 9999),
                    'time' => '10:15 WIB',
                    'item' => 'Nasi Kapau Komplit Rendang',
                    'qty' => 1,
                    'amount' => 28000,
                    'method' => 'TUNAI',
                ],
                [
                    'id' => 'TRX-' . rand(1000, 9999),
                    'time' => '09:05 WIB',
                    'item' => 'Teh Talua Spesial Jahe',
                    'qty' => 3,
                    'amount' => 45000,
                    'method' => 'TUNAI',
                ],
            ],
        ]);

        return response()->json([
            'success' => true,
            'date' => $today,
            'summary' => $summary,
        ]);
    }

    /**
     * Record a quick sale transaction.
     */
    public function recordTransaction(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'item' => 'required|string|max:100',
            'qty' => 'required|integer|min:1',
            'amount' => 'required|numeric|min:1000',
            'method' => 'required|string|in:TUNAI,QRIS,KOIN_EMAS',
        ]);

        $today = Carbon::now('Asia/Jakarta')->toDateString();
        $timeStr = Carbon::now('Asia/Jakarta')->format('H:i') . ' WIB';
        $cacheKey = "pos_summary_{$user->id}_{$today}";

        $summary = Cache::get($cacheKey, [
            'total_sales' => 0,
            'transaction_count' => 0,
            'gross_profit' => 0,
            'is_closed' => false,
            'recent_transactions' => [],
        ]);

        $newTrx = [
            'id' => 'TRX-' . rand(10000, 99999),
            'time' => $timeStr,
            'item' => $validated['item'],
            'qty' => intval($validated['qty']),
            'amount' => floatval($validated['amount']),
            'method' => $validated['method'],
        ];

        $summary['total_sales'] += $newTrx['amount'];
        $summary['transaction_count'] += 1;
        $summary['gross_profit'] += ($newTrx['amount'] * 0.35); // 35% margin estimasi
        array_unshift($summary['recent_transactions'], $newTrx);
        $summary['recent_transactions'] = array_slice($summary['recent_transactions'], 0, 10);

        Cache::put($cacheKey, $summary, now()->addDays(2));

        // Bonus XP to user for active cashier bookkeeping
        $userModel = User::find($user->id);
        $userModel->experience_points += 20;
        $userModel->save();

        return response()->json([
            'success' => true,
            'message' => 'Transaksi kasir berhasil dicatat ke pembukuan POS!',
            'transaction' => $newTrx,
            'summary' => $summary,
        ]);
    }

    /**
     * Close the register for the day (Tutup Buku Kasir).
     */
    public function closeRegister(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $today = Carbon::now('Asia/Jakarta')->toDateString();
        $cacheKey = "pos_summary_{$user->id}_{$today}";

        $summary = Cache::get($cacheKey, [
            'total_sales' => 0,
            'transaction_count' => 0,
            'gross_profit' => 0,
            'is_closed' => false,
            'recent_transactions' => [],
        ]);

        $summary['is_closed'] = true;
        $summary['closed_at'] = Carbon::now('Asia/Jakarta')->toDateTimeString();
        Cache::put($cacheKey, $summary, now()->addDays(2));

        // Reward for closing register properly
        $wallet = Wallet::firstOrCreate(['user_id' => $user->id]);
        $wallet->silver_balance += 500;
        $wallet->save();

        $userModel = User::find($user->id);
        $userModel->experience_points += 50;
        $userModel->save();

        return response()->json([
            'success' => true,
            'message' => 'Tutup buku kasir harian sukses! Reward +500 Perak & +50 XP telah dikreditkan ke brankas Anda.',
            'summary' => $summary,
        ]);
    }

    /**
     * Quick persona switcher for testing / profile.
     */
    public function switchPersona(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'persona' => 'required|string|in:UMKM,PEMUDA,GURU,PELAJAR',
        ]);

        $userModel = User::find($user->id);
        $userModel->persona = $validated['persona'];
        $userModel->save();

        return response()->json([
            'success' => true,
            'message' => "Persona berhasil diubah menjadi {$validated['persona']}!",
            'persona' => $userModel->persona,
        ]);
    }

    /**
     * Upgrade license or subscription plan.
     */
    public function upgradeLicense(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'target_tier' => 'required|string|in:PETUALANG,SAUDAGAR',
            'payment_method' => 'required|string|in:FIAT,GOLD',
        ]);

        $userModel = User::find($user->id);
        $wallet = Wallet::firstOrCreate(['user_id' => $user->id]);

        $costGold = $validated['target_tier'] === 'SAUDAGAR' ? 150 : 80;

        if ($validated['payment_method'] === 'GOLD') {
            if ($wallet->gold_balance < $costGold) {
                return response()->json([
                    'success' => false,
                    'message' => "Saldo Koin Emas tidak mencukupi. Diperlukan {$costGold} Koin Emas.",
                ], 400);
            }
            $wallet->gold_balance -= $costGold;
            $wallet->save();
        }

        $userModel->license_type = $validated['target_tier'];
        $userModel->save();

        return response()->json([
            'success' => true,
            'message' => "Selamat! Lisensi Anda berhasil ditingkatkan ke tingkat {$validated['target_tier']}!",
            'license_type' => $userModel->license_type,
            'wallet' => $wallet,
        ]);
    }
}
