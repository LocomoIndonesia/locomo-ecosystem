<?php

namespace App\Services;

use App\Models\BaitulMalPool;
use App\Models\BaitulMalPeriod;
use App\Models\BaitulMalDonation;
use App\Models\BaitulMalDistribution;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Ledger;
use App\Models\LedgerEntry;
use Illuminate\Support\Facades\DB;
use Exception;

class BaitulMalService
{
    /**
     * Get or initialize the central Baitul Mal pool.
     */
    public static function getPool(): BaitulMalPool
    {
        $pool = BaitulMalPool::first();
        if (!$pool) {
            $pool = BaitulMalPool::create([
                'name' => 'Kas Sosial Baitul Mal PitiCoin & Gunung Mas',
                'total_collected_gold' => 0,
                'active_balance_gold' => 0,
                'total_distributed_gold' => 0,
                'total_donations_count' => 0,
                'total_beneficiaries_count' => 0,
            ]);
        }
        return $pool;
    }

    /**
     * Mask user name for privacy (UU PDP No. 27/2022).
     */
    public static function maskName(?string $name, bool $isAnonymous = false): string
    {
        if ($isAnonymous || empty($name)) {
            return 'Hamba Allah';
        }

        $parts = explode(' ', trim($name));
        $maskedParts = [];

        foreach ($parts as $part) {
            $len = mb_strlen($part);
            if ($len <= 2) {
                $maskedParts[] = $part . '*';
            } elseif ($len <= 4) {
                $maskedParts[] = mb_substr($part, 0, 1) . str_repeat('*', $len - 1);
            } else {
                $maskedParts[] = mb_substr($part, 0, 2) . str_repeat('*', $len - 3) . mb_substr($part, -1);
            }
        }

        return implode(' ', $maskedParts);
    }

    /**
     * Mask WhatsApp / phone number for privacy.
     */
    public static function maskPhone(?string $phone): string
    {
        if (empty($phone)) {
            return '0812****0000';
        }

        $clean = preg_replace('/[^0-9]/', '', $phone);
        $len = strlen($clean);

        if ($len <= 6) {
            return substr($clean, 0, 2) . '****';
        }

        return substr($clean, 0, 4) . '****' . substr($clean, -3);
    }

    /**
     * Process a gold donation with strict ledger invariants.
     */
    public static function donate(User $user, int $amountGold, ?string $prayerMessage = null, bool $isAnonymous = false): BaitulMalDonation
    {
        if ($amountGold < 1) {
            throw new Exception('Nominal sedekah minimal adalah 1 Koin Emas.');
        }

        return DB::transaction(function () use ($user, $amountGold, $prayerMessage, $isAnonymous) {
            // 1. Lock wallet for update
            $wallet = Wallet::where('user_id', $user->id)->lockForUpdate()->first();
            if (!$wallet || $wallet->gold_balance < $amountGold) {
                $current = $wallet ? $wallet->gold_balance : 0;
                throw new Exception("Saldo Koin Emas Anda tidak mencukupi (Tersedia: {$current} Emas, Butuh: {$amountGold} Emas).");
            }

            // 2. Deduct gold from user wallet
            $wallet->decrement('gold_balance', $amountGold);

            // 3. Increment Baitul Mal pool
            $pool = BaitulMalPool::lockForUpdate()->first();
            if (!$pool) {
                $pool = self::getPool();
            }
            $pool->increment('active_balance_gold', $amountGold);
            $pool->increment('total_collected_gold', $amountGold);
            $pool->increment('total_donations_count', 1);

            // 4. Double-entry ledger audit
            $ledger = Ledger::create([
                'description' => "Sedekah Kas Sosial Baitul Mal Gunung Mas oleh {$user->name}",
                'reference_type' => 'BAITUL_MAL_DONATION',
            ]);

            LedgerEntry::create([
                'ledger_id' => $ledger->id,
                'wallet_id' => $user->id,
                'currency' => 'GOLD',
                'type' => 'DEBIT',
                'amount' => $amountGold,
            ]);

            // 5. Create donation record
            $maskedName = self::maskName($user->name, $isAnonymous);
            $maskedPhone = self::maskPhone($user->whatsapp);

            $donation = BaitulMalDonation::create([
                'user_id' => $user->id,
                'amount_gold' => $amountGold,
                'masked_donor_name' => $maskedName,
                'masked_donor_phone' => $maskedPhone,
                'prayer_message' => $prayerMessage ? strip_tags(trim($prayerMessage)) : null,
                'is_anonymous' => $isAnonymous,
                'aamiin_count' => 0,
            ]);

            // 6. Award altruism EXP
            ExperienceService::addExperience(
                $user->id,
                max(10, min(100, intval($amountGold * 2))),
                'COMMUNITY',
                "Sedekah Baitul Mal: {$amountGold} Koin Emas"
            );

            return $donation;
        });
    }

    /**
     * Community Amen (Aamiin) counter increment.
     */
    public static function aamiin(string $donationId): int
    {
        $donation = BaitulMalDonation::findOrFail($donationId);
        $donation->increment('aamiin_count');
        return $donation->aamiin_count;
    }

    /**
     * Seed realistic sample periods if empty so the interface looks alive & transparent.
     */
    public static function seedSamplePeriodsIfEmpty(): void
    {
        if (BaitulMalPeriod::count() === 0) {
            $period1 = BaitulMalPeriod::create([
                'period_name' => 'Batch #1: Berkah Ekspedisi Gunung Mas (September 2026)',
                'distribution_date' => now()->subDays(2),
                'total_gold_distributed' => 50000,
                'beneficiaries_count' => 8,
                'status' => 'DISTRIBUTED',
                'notes' => 'Penyaluran modal awal petualang & perlengkapan penjelajah dhuafa faksi Manusia dan Nisnas.',
            ]);

            $sampleMustahiks = [
                ['name' => 'Buyung Pandeka', 'cat' => 'PENJELAJAH_PEMULA', 'amount' => 6000, 'note' => 'Bantuan modal komitmen staking ekspedisi'],
                ['name' => 'Siti Rohana', 'cat' => 'DHUAFA_MITRA', 'amount' => 7500, 'note' => 'Bantuan sembako keluarga mitra Saudagar'],
                ['name' => 'Agus Santoso', 'cat' => 'PENJELAJAH_PEMULA', 'amount' => 6000, 'note' => 'Penggantian perbekalan ekspedisi'],
                ['name' => 'Hamba Allah', 'cat' => 'DANA_DARURAT', 'amount' => 10000, 'note' => 'Bantuan darurat kecelakaan penjelajah'],
                ['name' => 'Zulfikar Ahmad', 'cat' => 'PENJELAJAH_PEMULA', 'amount' => 6500, 'note' => 'Subsidi lisensi petualang muda'],
                ['name' => 'Nurmala Dewi', 'cat' => 'DHUAFA_MITRA', 'amount' => 5000, 'note' => 'Bantuan modal kerajinan lokal'],
                ['name' => 'Rian Kurniawan', 'cat' => 'PENJELAJAH_PEMULA', 'amount' => 4500, 'note' => 'Insentif penjelajah berprestasi'],
                ['name' => 'Hamba Allah', 'cat' => 'DANA_DARURAT', 'amount' => 4500, 'note' => 'Santunan dhuafa pesisir'],
            ];

            foreach ($sampleMustahiks as $item) {
                BaitulMalDistribution::create([
                    'period_id' => $period1->id,
                    'masked_beneficiary_name' => self::maskName($item['name'], $item['name'] === 'Hamba Allah'),
                    'beneficiary_category' => $item['cat'],
                    'amount_gold' => $item['amount'],
                    'distribution_note' => $item['note'],
                    'disbursed_at' => now()->subDays(2),
                ]);
            }
        }
    }
}
