<?php

namespace App\Http\Controllers;

use App\Models\EcosystemService;
use App\Models\ServiceLicense;
use App\Models\User;
use App\Models\Wallet;
use App\Services\LedgerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ServiceHubController extends Controller
{
    /**
     * Check if a beneficiary identifier (WhatsApp or ID) exists in PitiCoin.
     */
    public function checkBeneficiary(Request $request)
    {
        $request->validate([
            'identifier' => 'required|string',
        ]);

        $raw = trim($request->input('identifier'));
        $cleanPhone = preg_replace('/[^0-9]/', '', $raw);

        $user = User::where('id', $raw)
            ->orWhere('username', $raw)
            ->when($cleanPhone, function ($query) use ($cleanPhone) {
                return $query->orWhere('whatsapp', $cleanPhone)
                             ->orWhere('whatsapp', 'like', '%' . substr($cleanPhone, -9));
            })
            ->first();

        if ($user) {
            return response()->json([
                'exists' => true,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'username' => $user->username,
                    'whatsapp' => $user->whatsapp,
                    'faction' => $user->faction,
                    'level' => $user->level,
                    'license_type' => $user->license_type,
                ],
                'message' => 'Pengguna PitiCoin terverifikasi: ' . $user->name,
            ]);
        }

        return response()->json([
            'exists' => false,
            'message' => 'Nomor belum terdaftar di PitiCoin. Penerima akan otomatis didaftarkan sebagai Downline Level 1 Anda.',
        ]);
    }

    /**
     * Display the dynamic multi-step onboarding wizard for a service.
     */
    public function showOnboard($code, Request $request)
    {
        $service = EcosystemService::where('code', $code)
            ->where('is_active', true)
            ->firstOrFail();

        $authUser = Auth::user();
        $wallet = Wallet::where('user_id', $authUser->id)->first();

        return Inertia::render('Services/OnboardWizard', [
            'service' => [
                'id' => $service->id,
                'name' => $service->name,
                'code' => $service->code,
                'icon' => $service->icon,
                'category' => $service->category,
                'description' => $service->description,
                'wizard_schema' => $service->wizard_schema,
                'price_gold' => $service->price_gold,
                'price_fiat' => (float) $service->price_fiat,
                'service_url' => $service->service_url,
            ],
            'auth_user' => [
                'id' => $authUser->id,
                'name' => $authUser->name,
                'username' => $authUser->username,
                'whatsapp' => $authUser->whatsapp,
                'level' => $authUser->level,
                'faction' => $authUser->faction,
            ],
            'gold_balance' => $wallet ? (int) $wallet->gold_balance : 0,
        ]);
    }

    /**
     * Process onboarding submission, user binding, payment via LedgerService, and license generation.
     */
    public function submitOnboard($code, Request $request, LedgerService $ledgerService)
    {
        $service = EcosystemService::where('code', $code)
            ->where('is_active', true)
            ->firstOrFail();

        $request->validate([
            'beneficiary_identifier' => 'required|string',
            'beneficiary_name' => 'nullable|string|max:150',
            'payment_method' => 'required|in:GOLD,FIAT',
            'form_data' => 'required|array',
        ]);

        $authUser = Auth::user();
        $identifier = trim($request->input('beneficiary_identifier'));
        $cleanPhone = preg_replace('/[^0-9]/', '', $identifier);
        $paymentMethod = $request->input('payment_method');
        $formData = $request->input('form_data');

        return DB::transaction(function () use ($service, $authUser, $identifier, $cleanPhone, $paymentMethod, $formData, $request, $ledgerService) {
            // 1. Resolve Beneficiary User
            $beneficiaryUser = User::where('id', $identifier)
                ->orWhere('username', $identifier)
                ->when($cleanPhone, function ($query) use ($cleanPhone) {
                    return $query->orWhere('whatsapp', $cleanPhone)
                                 ->orWhere('whatsapp', 'like', '%' . substr($cleanPhone, -9));
                })
                ->first();

            if (!$beneficiaryUser) {
                // Auto-create new user under the current registrant as Downline L1
                $name = $request->input('beneficiary_name') ?: 'Mitra ' . substr($cleanPhone, -4);
                $uniqueUsername = 'mitra_' . ($cleanPhone ? substr($cleanPhone, -6) : Str::random(6));

                $beneficiaryUser = User::create([
                    'name' => $name,
                    'username' => $uniqueUsername,
                    'whatsapp' => $cleanPhone ?: '08' . rand(10000000, 99999999),
                    'password' => Hash::make('Locomo@' . substr($cleanPhone, -4)),
                    'role' => 'USER',
                    'faction' => $authUser->faction ?? 'HUMAN',
                    'role_class' => 'Pedagang',
                    'persona' => 'UMKM',
                    'license_type' => 'OBSERVER',
                    'license_status' => 'ACTIVE',
                    'level' => 1,
                    'experience_points' => 0,
                    'upline_id' => $authUser->id,
                    'grand_upline_id' => $authUser->upline_id,
                ]);

                Wallet::create([
                    'user_id' => $beneficiaryUser->id,
                    'silver_balance' => 50000, // Welcome Silver gift for mining
                    'gold_balance' => 0,
                ]);
            }

            $targetType = ($beneficiaryUser->id === $authUser->id) ? 'SELF' : 'OTHER';

            // 2. Handle Payment Processing
            if ($paymentMethod === 'GOLD') {
                $costGold = (float) $service->price_gold;
                $payerWallet = Wallet::where('user_id', $authUser->id)->lockForUpdate()->first();

                if (!$payerWallet || $payerWallet->gold_balance < $costGold) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Saldo Koin Emas tidak mencukupi. Butuh ' . $costGold . ' Emas, saldo Anda ' . ($payerWallet ? $payerWallet->gold_balance : 0) . ' Emas.',
                    ], 422);
                }

                // Record Double-Entry Ledger Transaction
                if ($costGold > 0) {
                    $ledgerService->recordTransaction(
                        debitWalletId: $authUser->id,
                        creditWalletId: null, // System Liquidity Vault
                        amount: $costGold,
                        currency: 'GOLD',
                        description: "Aktivasi Lisensi {$service->name} (" . ($targetType === 'SELF' ? 'Pribadi' : 'Mitra: ' . $beneficiaryUser->name) . ")",
                        referenceType: EcosystemService::class,
                        referenceId: $service->id
                    );
                }

                $status = 'ACTIVE';
                $amountPaid = $costGold;
                $currency = 'GOLD';
            } else {
                // FIAT Payment (QRIS / Transfer)
                $status = 'PENDING_PAYMENT';
                $amountPaid = (float) $service->price_fiat;
                $currency = 'IDR';
            }

            // 3. Generate Official License Code
            $slugPrefix = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $service->code), 0, 3));
            $licenseCode = 'LIC-' . $slugPrefix . '-' . date('Y') . '-' . strtoupper(Str::random(6));

            // 4. Create Service License Record
            $license = ServiceLicense::create([
                'service_id' => $service->id,
                'user_id' => $beneficiaryUser->id,
                'registered_by_id' => $authUser->id,
                'target_type' => $targetType,
                'beneficiary_data' => $formData,
                'license_code' => $licenseCode,
                'status' => $status,
                'payment_method' => $paymentMethod,
                'amount_paid' => $amountPaid,
                'currency' => $currency,
                'expires_at' => now()->addYear(),
                'metadata' => [
                    'service_name' => $service->name,
                    'registrant_name' => $authUser->name,
                    'beneficiary_name' => $beneficiaryUser->name,
                ],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pendaftaran lisensi ' . $service->name . ' berhasil diproses!',
                'license' => $license,
                'redirect_url' => $service->service_url ? $service->service_url . '?license=' . $licenseCode : null,
            ]);
        });
    }
}
