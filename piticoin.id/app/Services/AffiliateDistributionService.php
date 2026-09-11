<?php

namespace App\Services;

use App\Models\AffiliateCommission;
use App\Models\EcosystemService;
use App\Models\OrderInstallment;
use App\Models\ServiceOrder;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AffiliateDistributionService
{
    protected LedgerService $ledgerService;

    public function __construct(LedgerService $ledgerService)
    {
        $this->ledgerService = $ledgerService;
    }

    /**
     * Distribusikan komisi profit sharing multi-level secara real-time
     * untuk setiap pembayaran termin / cicilan yang berhasil disetorkan.
     *
     * @param OrderInstallment $installment
     * @return array
     */
    public function distribute(OrderInstallment $installment): array
    {
        if ($installment->commissions_distributed) {
            Log::info("Komisi untuk termin #{$installment->id} sudah pernah didistribusikan sebelumnya.");
            return ['status' => 'ALREADY_DISTRIBUTED', 'commissions' => []];
        }

        $order = $installment->order()->with(['service', 'user'])->first();
        if (!$order || !$order->service) {
            Log::warning("Order atau Service tidak ditemukan untuk termin #{$installment->id}");
            return ['status' => 'INVALID_ORDER', 'commissions' => []];
        }

        $service = $order->service;
        $rules = $service->commission_rules;

        if (empty($rules) || empty($rules['levels'])) {
            Log::info("Service {$service->name} tidak memiliki konfigurasi komisi sharing profit aktif.");
            $installment->update(['commissions_distributed' => true]);
            return ['status' => 'NO_RULES', 'commissions' => []];
        }

        $maxDepth = (int) ($rules['depth'] ?? count($rules['levels']));
        $levelMap = [];
        foreach ($rules['levels'] as $rule) {
            $levelMap[(int) $rule['level']] = (float) ($rule['percentage'] ?? 0);
        }

        $createdCommissions = [];
        $beneficiaryUser = $order->user;
        $currentCursor = $beneficiaryUser;

        DB::transaction(function () use (
            $installment,
            $order,
            $service,
            $maxDepth,
            $levelMap,
            $beneficiaryUser,
            &$currentCursor,
            &$createdCommissions
        ) {
            for ($level = 1; $level <= $maxDepth; $level++) {
                // Cari upline langsung dari cursor
                $upline = $currentCursor->upline;

                if (!$upline) {
                    // Upline rantai telah habis, hentikan penelusuran
                    break;
                }

                $percentage = $levelMap[$level] ?? 0;

                if ($percentage > 0) {
                    $currency = $installment->currency;
                    $commissionGold = 0.0;
                    $commissionFiat = 0.0;
                    $payoutAmount = 0.0;

                    if ($currency === 'GOLD') {
                        $commissionGold = round(((float) $installment->amount_gold) * ($percentage / 100), 2);
                        $payoutAmount = $commissionGold;
                    } else {
                        $commissionFiat = round(((float) $installment->amount_fiat) * ($percentage / 100), 2);
                        $payoutAmount = $commissionFiat;
                    }

                    if ($payoutAmount > 0) {
                        // Catat pembukuan ganda (Double-Entry Ledger)
                        // DEBIT: System Pool Vault (null)
                        // CREDIT: Upline Wallet
                        $ledger = $this->ledgerService->recordTransaction(
                            debitWalletId: null, // Vault Likuiditas Sistem
                            creditWalletId: $upline->id,
                            amount: $payoutAmount,
                            currency: $currency,
                            description: "Profit Sharing [Level {$level}] Termin #{$installment->installment_number} {$service->name} dari {$beneficiaryUser->name}",
                            referenceType: AffiliateCommission::class,
                            referenceId: $installment->id
                        );

                        // Simpan record komisi
                        $commission = AffiliateCommission::create([
                            'service_id' => $service->id,
                            'service_order_id' => $order->id,
                            'order_installment_id' => $installment->id,
                            'beneficiary_user_id' => $beneficiaryUser->id,
                            'upline_user_id' => $upline->id,
                            'level' => $level,
                            'percentage' => $percentage,
                            'amount_gold' => $commissionGold,
                            'amount_fiat' => $commissionFiat,
                            'currency' => $currency,
                            'status' => 'SETTLED',
                            'ledger_id' => $ledger->id,
                        ]);

                        $createdCommissions[] = $commission;
                    }
                }

                // Pindah ke upline berikutnya
                $currentCursor = $upline;
            }

            // Tandai termin cicilan telah didistribusikan
            $installment->update([
                'commissions_distributed' => true,
                'status' => 'PAID',
                'paid_at' => $installment->paid_at ?: now(),
            ]);
        });

        Log::info("Komisi profit sharing untuk termin #{$installment->id} berhasil dibagikan ke " . count($createdCommissions) . " level upline.");

        return [
            'status' => 'SUCCESS',
            'commissions' => $createdCommissions,
        ];
    }
}
