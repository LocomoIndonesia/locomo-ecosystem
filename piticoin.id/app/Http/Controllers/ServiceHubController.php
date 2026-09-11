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

        $user = User::query()
            ->when(Str::isUuid($raw), function ($q) use ($raw) {
                return $q->where('id', $raw);
            }, function ($q) use ($raw, $cleanPhone) {
                return $q->where('username', $raw)
                    ->when($cleanPhone, function ($query) use ($cleanPhone) {
                        return $query->orWhere('whatsapp', $cleanPhone)
                                     ->orWhere('whatsapp', 'like', '%' . substr($cleanPhone, -9));
                    });
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
                'service_type' => $service->service_type,
                'allows_installments' => (bool) $service->allows_installments,
                'min_down_payment_gold' => (float) $service->min_down_payment_gold,
                'min_down_payment_fiat' => (float) $service->min_down_payment_fiat,
                'commission_rules' => $service->commission_rules,
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
            $beneficiaryUser = User::query()
                ->when(Str::isUuid($identifier), function ($q) use ($identifier) {
                    return $q->where('id', $identifier);
                }, function ($q) use ($identifier, $cleanPhone) {
                    return $q->where('username', $identifier)
                        ->when($cleanPhone, function ($query) use ($cleanPhone) {
                            return $query->orWhere('whatsapp', $cleanPhone)
                                         ->orWhere('whatsapp', 'like', '%' . substr($cleanPhone, -9));
                        });
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

            // 2. Resolve Dynamic Package Tier Pricing (if schema provides packages)
            $costGold = (float) $service->price_gold;
            $costFiat = (float) $service->price_fiat;
            $selectedPackageName = 'Bisnis';

            if (isset($formData['package_tier'])) {
                $tierStr = (string) $formData['package_tier'];
                if (stripos($tierStr, 'Basic') !== false || stripos($tierStr, '40') !== false) {
                    $costGold = 40.0;
                    $costFiat = 50000.0;
                    $selectedPackageName = 'Basic';
                } elseif (stripos($tierStr, 'Enterprise') !== false || stripos($tierStr, 'VIP') !== false || stripos($tierStr, '160') !== false) {
                    $costGold = 160.0;
                    $costFiat = 200000.0;
                    $selectedPackageName = 'Enterprise VIP';
                } else {
                    $costGold = 80.0;
                    $costFiat = 100000.0;
                    $selectedPackageName = 'Bisnis';
                }
            }

            // 3. Resolve Payment Type (Full vs Installment / Staged Payment)
            $paymentType = $request->input('payment_type', 'FULL');
            $isInstallment = ($paymentType === 'INSTALLMENT' && $service->allows_installments);

            $chargeGold = $costGold;
            $chargeFiat = $costFiat;

            if ($isInstallment) {
                // Minimum down payment validation
                $minDpGold = (float) ($service->min_down_payment_gold ?: ($costGold * 0.2));
                $minDpFiat = (float) ($service->min_down_payment_fiat ?: ($costFiat * 0.2));

                $customDp = (float) $request->input('down_payment_amount', 0);
                if ($paymentMethod === 'GOLD') {
                    $chargeGold = max($minDpGold, $customDp ?: $minDpGold);
                    if ($chargeGold > $costGold) $chargeGold = $costGold;
                } else {
                    $chargeFiat = max($minDpFiat, $customDp ?: $minDpFiat);
                    if ($chargeFiat > $costFiat) $chargeFiat = $costFiat;
                }
            }

            // 4. Handle Payment Processing via Ledger
            if ($paymentMethod === 'GOLD') {
                $payerWallet = Wallet::where('user_id', $authUser->id)->lockForUpdate()->first();

                if (!$payerWallet || $payerWallet->gold_balance < $chargeGold) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Saldo Koin Emas tidak mencukupi. Butuh ' . $chargeGold . ' Emas, saldo Anda ' . ($payerWallet ? $payerWallet->gold_balance : 0) . ' Emas.',
                    ], 422);
                }

                // Record Double-Entry Ledger Transaction
                if ($chargeGold > 0) {
                    $ledgerService->recordTransaction(
                        debitWalletId: $authUser->id,
                        creditWalletId: null, // System Liquidity Vault
                        amount: $chargeGold,
                        currency: 'GOLD',
                        description: "Pembayaran {$service->name} [Paket {$selectedPackageName}] (" . ($isInstallment ? 'Uang Muka / Termin 1' : 'Pelunasan Penuh') . ") untuk " . ($targetType === 'SELF' ? 'Pribadi' : 'Mitra: ' . $beneficiaryUser->name),
                        referenceType: EcosystemService::class,
                        referenceId: $service->id
                    );
                }

                $status = 'ACTIVE';
                $amountPaid = $chargeGold;
                $currency = 'GOLD';
            } else {
                // FIAT Payment (QRIS / Transfer)
                $status = 'PENDING_PAYMENT';
                $amountPaid = $chargeFiat;
                $currency = 'IDR';
            }

            // 5. Generate Official License Code
            $slugPrefix = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $service->code), 0, 3));
            $licenseCode = 'LIC-' . $slugPrefix . '-' . date('Y') . '-' . strtoupper(Str::random(6));

            // 6. Create Service Order Record
            $orderStatus = ($amountPaid >= ($currency === 'GOLD' ? $costGold : $costFiat)) ? 'COMPLETED' : 'ACTIVE';
            $serviceOrder = \App\Models\ServiceOrder::create([
                'service_id' => $service->id,
                'user_id' => $beneficiaryUser->id,
                'registered_by_id' => $authUser->id,
                'license_code' => $licenseCode,
                'package_tier' => $selectedPackageName,
                'payment_type' => $isInstallment ? 'INSTALLMENT' : 'FULL',
                'total_amount_gold' => $costGold,
                'total_amount_fiat' => $costFiat,
                'paid_amount_gold' => ($currency === 'GOLD') ? $chargeGold : 0,
                'paid_amount_fiat' => ($currency === 'IDR') ? $chargeFiat : 0,
                'status' => $orderStatus,
                'order_data' => $formData,
            ]);

            // 7. Create Initial Order Installment (Termin 1 / DP)
            $installment = \App\Models\OrderInstallment::create([
                'service_order_id' => $serviceOrder->id,
                'installment_number' => 1,
                'amount_gold' => ($currency === 'GOLD') ? $chargeGold : 0,
                'amount_fiat' => ($currency === 'IDR') ? $chargeFiat : 0,
                'currency' => $currency,
                'payment_method' => $paymentMethod,
                'status' => ($paymentMethod === 'GOLD') ? 'PAID' : 'PENDING',
                'commissions_distributed' => false,
                'paid_at' => ($paymentMethod === 'GOLD') ? now() : null,
                'notes' => $isInstallment ? 'Uang Muka (DP) / Termin 1' : 'Pembayaran Penuh',
            ]);

            // 8. Distribute Real-Time Profit Sharing to Uplines if payment settled
            if ($paymentMethod === 'GOLD' && $chargeGold > 0) {
                $distributionService = app(\App\Services\AffiliateDistributionService::class);
                $distributionService->distribute($installment);
            }

            $redirectUrl = $service->service_url ? $service->service_url . '?license=' . $licenseCode : null;
            $serviceMetadata = [];

            // 9. Ecosystem Dispatch (External vs Internal)
            if ($service->service_type === 'EXTERNAL' && $service->code === 'unimart') {
                $unimartClient = app(\App\Services\Ecosystem\UnimartClient::class);
                $shopName = $formData['shop_name'] ?? ($formData['business_name'] ?? ($request->input('beneficiary_name') ?: 'Toko ' . $beneficiaryUser->name));
                $domainSlug = !empty($formData['domain']) ? Str::slug($formData['domain']) : Str::slug($shopName);

                $unimartRes = $unimartClient->registerStore([
                    'license_code' => $licenseCode,
                    'package' => $selectedPackageName,
                    'business_name' => $shopName,
                    'domain' => $domainSlug,
                    'business_type' => $formData['business_category'] ?? ($formData['business_type'] ?? null),
                    'business_address' => $formData['shop_address'] ?? ($formData['business_address'] ?? null),
                    'business_description' => $formData['shop_description'] ?? ($formData['business_description'] ?? null),
                    'owner_name' => $beneficiaryUser->name,
                    'owner_phone' => $beneficiaryUser->whatsapp,
                    'owner_email' => $formData['owner_email'] ?? null,
                    'piticoin_user_id' => $beneficiaryUser->id,
                    'registered_by_id' => $authUser->id,
                    'payment_method' => $paymentMethod,
                    'duration_months' => 1,
                ]);

                if (!empty($unimartRes['store_url'])) {
                    $redirectUrl = $unimartRes['store_url'];
                    $serviceMetadata = [
                        'unimart_store_url' => $unimartRes['store_url'],
                        'unimart_menu_url' => $unimartRes['menu_url'] ?? null,
                        'unimart_login_url' => $unimartRes['login_url'] ?? null,
                        'unimart_initial_password' => $unimartRes['initial_password'] ?? null,
                    ];
                }
            } elseif ($service->service_type === 'INTERNAL') {
                // Layanan Internal: tidak memanggil pihak ketiga, cukup mencatat kontrak & pohon jaringan
                $redirectUrl = '/services/' . $service->code . '/orders/' . $serviceOrder->id;
                $serviceMetadata = [
                    'internal_service' => true,
                    'order_id' => $serviceOrder->id,
                ];
            }

            // 10. Create Service License Record
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
                'metadata' => array_merge([
                    'service_name' => $service->name,
                    'package_tier' => $selectedPackageName,
                    'order_id' => $serviceOrder->id,
                    'payment_type' => $isInstallment ? 'INSTALLMENT' : 'FULL',
                    'registrant_name' => $authUser->name,
                    'beneficiary_name' => $beneficiaryUser->name,
                ], $serviceMetadata),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pendaftaran lisensi ' . $service->name . ' berhasil diproses!',
                'license' => $license,
                'order' => $serviceOrder,
                'redirect_url' => $redirectUrl,
            ]);
        });
    }

    /**
     * Endpoint untuk membayar cicilan / termin berikutnya dari pemesanan layanan.
     */
    public function payInstallment(Request $request, $orderId, LedgerService $ledgerService, \App\Services\AffiliateDistributionService $distributionService)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|in:GOLD,FIAT',
            'notes' => 'nullable|string|max:255',
        ]);

        $order = \App\Models\ServiceOrder::with(['service', 'user', 'registeredBy'])->findOrFail($orderId);
        $authUser = Auth::user();
        $paymentMethod = $request->input('payment_method');
        $payAmount = (float) $request->input('amount');

        if ($order->status === 'COMPLETED') {
            return response()->json([
                'success' => false,
                'message' => 'Pemesanan layanan ini sudah lunas.',
            ], 422);
        }

        return DB::transaction(function () use ($order, $authUser, $paymentMethod, $payAmount, $request, $ledgerService, $distributionService) {
            $currency = ($paymentMethod === 'GOLD') ? 'GOLD' : 'IDR';

            if ($paymentMethod === 'GOLD') {
                $payerWallet = Wallet::where('user_id', $authUser->id)->lockForUpdate()->first();
                if (!$payerWallet || $payerWallet->gold_balance < $payAmount) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Saldo Koin Emas tidak mencukupi untuk membayar cicilan ini.',
                    ], 422);
                }

                // Double-Entry Ledger
                $ledgerService->recordTransaction(
                    debitWalletId: $authUser->id,
                    creditWalletId: null,
                    amount: $payAmount,
                    currency: 'GOLD',
                    description: "Pembayaran Cicilan Layanan {$order->service->name} untuk {$order->user->name}",
                    referenceType: \App\Models\ServiceOrder::class,
                    referenceId: $order->id
                );

                $status = 'PAID';
            } else {
                $status = 'PENDING';
            }

            // Hitung nomor termin berikutnya
            $nextNumber = ($order->installments()->max('installment_number') ?: 0) + 1;

            $installment = \App\Models\OrderInstallment::create([
                'service_order_id' => $order->id,
                'installment_number' => $nextNumber,
                'amount_gold' => ($currency === 'GOLD') ? $payAmount : 0,
                'amount_fiat' => ($currency === 'IDR') ? $payAmount : 0,
                'currency' => $currency,
                'payment_method' => $paymentMethod,
                'status' => $status,
                'commissions_distributed' => false,
                'paid_at' => ($status === 'PAID') ? now() : null,
                'notes' => $request->input('notes') ?: "Pembayaran Termin #{$nextNumber}",
            ]);

            // Update agregasi saldo terbayar di order
            if ($status === 'PAID') {
                if ($currency === 'GOLD') {
                    $order->paid_amount_gold += $payAmount;
                } else {
                    $order->paid_amount_fiat += $payAmount;
                }

                // Cek apakah sudah lunas
                $isFullyPaid = ($currency === 'GOLD')
                    ? ($order->paid_amount_gold >= $order->total_amount_gold)
                    : ($order->paid_amount_fiat >= $order->total_amount_fiat);

                if ($isFullyPaid) {
                    $order->status = 'COMPLETED';
                }
                $order->save();

                // Distribusikan profit sharing real-time ke para upline
                $distResult = $distributionService->distribute($installment);
            }

            return response()->json([
                'success' => true,
                'message' => "Pembayaran termin #{$nextNumber} berhasil diproses!",
                'installment' => $installment,
                'order' => $order->fresh(),
            ]);
        });
    }
}
