<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Wallet;
use App\Services\ExperienceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Exception;

class OnboardingController extends Controller
{
    /**
     * Step 2: Show Awakening (Faction Selection)
     */
    public function showAwakening()
    {
        return Inertia::render('Onboarding/Awakening', [
            'user' => Auth::user()
        ]);
    }

    /**
     * Save Faction Selection and proceed to Step 3.
     */
    public function saveAwakening(Request $request)
    {
        $request->validate([
            'faction' => 'required|string|in:NISNAS,HUMAN,ANDROID',
        ]);

        $user = Auth::user();
        
        // Save faction, increase onboarding step to 3
        $user->update([
            'faction' => $request->input('faction'),
            'onboarding_step' => 3
        ]);

        // Award base starter experience points
        ExperienceService::addExperience(
            $user->id,
            100,
            'ONBOARDING',
            "Terbangun di Dunia Piticoin dengan Faksi: " . $request->input('faction')
        );

        return redirect()->route('onboarding.path');
    }

    /**
     * Step 3: Show Path (Class Role Selection)
     */
    public function showPath()
    {
        return Inertia::render('Onboarding/Path', [
            'user' => Auth::user()
        ]);
    }

    /**
     * Save Class Role and proceed to Step 4.
     */
    public function savePath(Request $request)
    {
        $request->validate([
            'role_class' => 'required|string|in:PENDEKAR,RANGER,SPESIALIS,MENTALIST',
        ]);

        $user = Auth::user();
        
        $user->update([
            'role_class' => $request->input('role_class'),
            'onboarding_step' => 4
        ]);

        // Award experience points for class choice
        ExperienceService::addExperience(
            $user->id,
            50,
            'ONBOARDING',
            "Memilih Jalan Hidup sebagai: " . $request->input('role_class')
        );

        return redirect()->route('onboarding.brotherhood');
    }

    /**
     * Step 4: Show Brotherhood (Referral Code / Caravan)
     */
    public function showBrotherhood()
    {
        return Inertia::render('Onboarding/Brotherhood', [
            'user' => Auth::user()
        ]);
    }

    /**
     * Save Referral Code and proceed to Step 5.
     */
    public function saveBrotherhood(Request $request)
    {
        // Referral/Upline code is optional, but if provided it should be a valid WhatsApp of an existing user
        $request->validate([
            'upline_whatsapp' => 'nullable|string|exists:users,whatsapp',
        ]);

        $user = Auth::user();
        $uplineWhatsApp = $request->input('upline_whatsapp');

        if ($uplineWhatsApp) {
            $upline = User::where('whatsapp', $uplineWhatsApp)->first();
            
            // Prevent self-referral
            if ($upline->id === $user->id) {
                return back()->withErrors(['upline_whatsapp' => 'Anda tidak bisa mendaftarkan diri sendiri sebagai upline.']);
            }

            // Establish upline and grand upline
            $user->update([
                'upline_id' => $upline->id,
                'grand_upline_id' => $upline->upline_id, // upline's upline
                'onboarding_step' => 5
            ]);

            // Give referral connection experience bonus
            ExperienceService::addExperience(
                $user->id,
                150,
                'ONBOARDING',
                "Bergabung dalam Karavan Saudagar: " . $upline->name
            );
        } else {
            $user->update([
                'onboarding_step' => 5
            ]);
        }

        return redirect()->route('onboarding.pledge');
    }

    /**
     * Step 5: Show Pledge (License Purchase Checkout)
     */
    public function showPledge()
    {
        return Inertia::render('Onboarding/Pledge', [
            'user' => Auth::user()
        ]);
    }

    /**
     * Purchase License (Pledge) and finalize onboarding.
     */
    public function savePledge(Request $request)
    {
        $request->validate([
            'license_type' => 'required|string|in:PEMULA,PETUALANG,SAUDAGAR',
        ]);

        $user = Auth::user();
        $licenseType = $request->input('license_type');

        // Determine silver coin rewards
        $silverReward = 0;
        $xpReward = 0;

        switch ($licenseType) {
            case 'PEMULA':
                $silverReward = 50000;
                $xpReward = 300;
                break;
            case 'PETUALANG':
                $silverReward = 100000;
                $xpReward = 600;
                break;
            case 'SAUDAGAR':
                $silverReward = 200000;
                $xpReward = 1200;
                break;
        }

        // Finalize User and Wallets
        \Illuminate\Support\Facades\DB::transaction(function () use ($user, $licenseType, $silverReward, $xpReward) {
            $user->update([
                'license_type' => $licenseType,
                'license_status' => 'ACTIVE',
                'role' => 'MERCHANT', // upgrade to merchant tier
                'onboarding_step' => 6 // complete onboarding
            ]);

            $wallet = Wallet::firstOrCreate(
                ['user_id' => $user->id],
                ['silver_balance' => 0, 'gold_balance' => 0]
            );
            $wallet->increment('silver_balance', $silverReward);

            // Give massive license purchase XP
            ExperienceService::addExperience(
                $user->id,
                $xpReward,
                'LICENSE_UPGRADE',
                "Mengaktifkan Piagam Lisensi: " . $licenseType
            );

            // Add 25% of purchase value (converted to rupiah: Pemula 50rb -> 12.5rb, etc.) to liquidity pool
            $purchasePrices = [
                'PEMULA' => 50000,
                'PETUALANG' => 100000,
                'SAUDAGAR' => 200000
            ];
            $allocation = intval($purchasePrices[$licenseType] * 25 / 100);
            
            $lp = \App\Models\LiquidityPoolSystem::find(1);
            if ($lp) {
                $lp->increment('total_fiat_in_bank', $allocation);
            }
        });

        return redirect()->route('dashboard')->with('message', "Sumpah Saudagar sukses! Selamat berpetualang!");
    }
}
