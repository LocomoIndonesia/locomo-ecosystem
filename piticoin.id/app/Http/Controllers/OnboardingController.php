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

        return redirect()->route('onboarding.pledge');
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
     * Purchase License (Pledge) - Create Pending Order & Redirect to Payment.
     */
    public function savePledge(Request $request)
    {
        $request->validate([
            'license_type' => 'required|string|in:PEMULA,PETUALANG,SAUDAGAR',
        ]);

        $user = Auth::user();
        $licenseType = $request->input('license_type');

        // Set status to PENDING awaiting admin payment verification
        $user->update([
            'license_type' => $licenseType,
            'license_status' => 'PENDING',
            'onboarding_step' => 5
        ]);

        return redirect()->route('onboarding.pending')->with('message', "Sumpah Saudagar dicatat. Silakan selesaikan pembayaran Starter Kit.");
    }

    /**
     * Show Payment Invoice & Pending Verification Status.
     */
    public function showPending()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        // If license is already ACTIVE, unlock and forward to dashboard
        if ($user->license_status === 'ACTIVE') {
            return redirect()->route('dashboard');
        }

        $packageDetails = [
            'PEMULA' => [
                'name' => 'Paket Pemula',
                'amount' => 50000,
                'formatted' => 'Rp 50.000',
                'silver' => '50.000 Koin Perak',
            ],
            'PETUALANG' => [
                'name' => 'Paket Petualang',
                'amount' => 100000,
                'formatted' => 'Rp 100.000',
                'silver' => '100.000 Koin Perak',
            ],
            'SAUDAGAR' => [
                'name' => 'Paket Saudagar VIP',
                'amount' => 200000,
                'formatted' => 'Rp 200.000',
                'silver' => '200.000 Koin Perak',
            ],
        ];

        $pkgKey = in_array($user->license_type, ['PEMULA', 'PETUALANG', 'SAUDAGAR']) ? $user->license_type : 'PETUALANG';
        $pkg = $packageDetails[$pkgKey];

        $invoiceCode = 'INV-PTC-' . strtoupper(substr(md5($user->id . $user->created_at), 0, 8));

        $invoice = [
            'code' => $invoiceCode,
            'package_name' => $pkg['name'],
            'amount' => $pkg['amount'],
            'formatted_amount' => $pkg['formatted'],
            'silver_reward' => $pkg['silver'],
            'bank_accounts' => [
                [
                    'bank' => 'Bank BCA',
                    'account_number' => '2170436132',
                    'account_name' => 'Ronal Dwi Putra'
                ],
                [
                    'bank' => 'Bank Mandiri',
                    'account_number' => '1110022659904',
                    'account_name' => 'Ronal Dwi Putra'
                ],
                [
                    'bank' => 'Bank BRI',
                    'account_number' => '547701042535534',
                    'account_name' => 'Ronal Dwi Putra'
                ]
            ],
            'ewallet_accounts' => [
                [
                    'name' => 'GoPay',
                    'account_number' => '082385327396',
                    'account_name' => 'PitiCoin / Locomo'
                ],
                [
                    'name' => 'OVO',
                    'account_number' => '085163518215',
                    'account_name' => 'PitiCoin / Locomo'
                ]
            ],
            'admin_contacts' => [
                [
                    'label' => 'Admin 1',
                    'whatsapp' => '6283130422715',
                    'formatted' => '+62 831 3042 2715'
                ],
                [
                    'label' => 'Admin 2',
                    'whatsapp' => '62895329429118',
                    'formatted' => '+62 895 3294 29118'
                ]
            ],
            'admin_whatsapp' => '6283130422715'
        ];

        return Inertia::render('Onboarding/Pending', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'whatsapp' => $user->whatsapp,
                'license_type' => $user->license_type,
                'license_status' => $user->license_status,
                'faction' => $user->faction,
                'role_class' => $user->role_class,
            ],
            'invoice' => $invoice
        ]);
    }

    /**
     * Admin confirmation for payment & license activation.
     */
    public function adminConfirmLicense(Request $request, $userId)
    {
        $adminKey = $request->header('x-admin-key') ?? $request->input('admin_key');
        if ($adminKey !== 'piticoin_admin_secret') {
            return response()->json(['success' => false, 'error' => 'Akses ditolak: Otorisasi Admin dibutuhkan.'], 403);
        }

        $user = User::findOrFail($userId);
        $licenseType = in_array($user->license_type, ['PEMULA', 'PETUALANG', 'SAUDAGAR']) ? $user->license_type : 'PETUALANG';

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

        \Illuminate\Support\Facades\DB::transaction(function () use ($user, $licenseType, $silverReward, $xpReward) {
            $user->update([
                'license_status' => 'ACTIVE',
                'role' => 'MERCHANT',
                'onboarding_step' => 6
            ]);

            $wallet = Wallet::firstOrCreate(
                ['user_id' => $user->id],
                ['silver_balance' => 0, 'gold_balance' => 0]
            );
            $wallet->increment('silver_balance', $silverReward);

            ExperienceService::addExperience(
                $user->id,
                $xpReward,
                'LICENSE_UPGRADE',
                "Piagam Lisensi Diverifikasi Admin: " . $licenseType
            );

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

        return response()->json([
            'success' => true,
            'message' => "Pembayaran terkonfirmasi! Pengguna {$user->name} berhasil diaktivasi sebagai Saudagar Aktif.",
            'user' => $user->fresh()
        ]);
    }
}
