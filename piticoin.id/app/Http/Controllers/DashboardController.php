<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Quest;
use App\Models\EscrowTransaction;
use App\Models\LiquidityPoolSystem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * Display the main gamified dashboard.
     */
    public function index()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        // 1. Fetch User and Wallet details
        $userWithWallet = User::with('wallet')->find($user->id);

        // 2. Fetch Active and Completed Escrow Transactions
        $escrows = EscrowTransaction::where('user_id', $user->id)
            ->with('quest')
            ->orderBy('created_at', 'desc')
            ->get();

        // 3. Fetch all active quests
        $quests = Quest::where('is_active', true)->get();

        // 4. Fetch Global Liquidity Pool (Gunung Mas)
        $lp = LiquidityPoolSystem::find(1);

        // 5. Fetch referral downlines (Level 1 & Level 2)
        $level1 = User::where('upline_id', $user->id)->with('wallet')->get();
        $level2 = User::where('grand_upline_id', $user->id)->with('wallet')->get();

        // 6. Calculate Passive Income dynamically
        $level1Ids = $level1->pluck('id')->toArray();
        $level2Ids = $level2->pluck('id')->toArray();

        // Level 1: 25% of reward gold
        $level1Escrows = EscrowTransaction::where('status', 'SUCCESS')
            ->whereIn('user_id', $level1Ids)
            ->with('quest')
            ->get();
            
        // Level 2: 10% of reward gold
        $level2Escrows = EscrowTransaction::where('status', 'SUCCESS')
            ->whereIn('user_id', $level2Ids)
            ->with('quest')
            ->get();

        $passiveIncome = 0;
        foreach ($level1Escrows as $e) {
            $passiveIncome += intval($e->quest->reward_amount_gold * 25 / 100);
        }
        foreach ($level2Escrows as $e) {
            $passiveIncome += intval($e->quest->reward_amount_gold * 10 / 100);
        }

        $userData = [
            'id' => $userWithWallet->id,
            'name' => $userWithWallet->name,
            'whatsapp' => $userWithWallet->whatsapp,
            'role' => $userWithWallet->role,
            'faction' => $userWithWallet->faction,
            'role_class' => $userWithWallet->role_class,
            'license_type' => $userWithWallet->license_type,
            'license_status' => $userWithWallet->license_status,
            'level' => $userWithWallet->level,
            'experience_points' => $userWithWallet->experience_points,
        ];

        $props = [
            'user' => $userData,
            'wallet' => $userWithWallet->wallet,
            'escrows' => $escrows,
            'quests' => $quests,
            'global_pool' => $lp,
            'downlines' => [
                'level_1' => $level1,
                'level_2' => $level2,
            ],
            'passive_income' => $passiveIncome,
        ];

        if ($userWithWallet->license_status !== 'ACTIVE') {
            return Inertia::render('Dashboard/Observer', $props);
        }

        return Inertia::render('Dashboard/Conqueror', $props);
    }
}
