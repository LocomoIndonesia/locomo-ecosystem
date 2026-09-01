<?php

namespace App\Services;

use App\Models\User;
use App\Models\Wallet;
use App\Models\Quest;
use App\Models\EscrowTransaction;
use App\Models\LiquidityPoolSystem;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

class WalletService
{
    /**
     * Logic 1: Starter Kit Purchase (Upgrade to Merchant)
     * Trigger: User pays Rp 100,000 via Payment Gateway.
     */
    public static function purchaseStarterKit(string $userId)
    {
        return DB::transaction(function () use ($userId) {
            // 1. Fetch user to verify
            $user = User::findOrFail($userId);

            // 2. Lock and retrieve the global liquidity pool (ID = 1)
            $lp = LiquidityPoolSystem::where('id', 1)->lockForUpdate()->first();
            if (!$lp) {
                throw new Exception('Liquidity pool system not initialized');
            }

            // 3. Update users.role to MERCHANT
            $user->update(['role' => 'MERCHANT']);

            // 4. Add 100,000 to wallets.silver_balance for this user_id
            $wallet = Wallet::firstOrCreate(
                ['user_id' => $userId],
                ['silver_balance' => 0, 'gold_balance' => 0]
            );

            $wallet->increment('silver_balance', 100000);

            // 5. Add 25,000 to liquidity_pool_system.total_fiat_in_bank (25% allocation)
            $lp->increment('total_fiat_in_bank', 25000);

            return [
                'role' => 'MERCHANT',
                'wallet' => $wallet->fresh(),
                'liquidity_pool' => $lp->fresh(),
            ];
        });
    }

    /**
     * Logic 2: Staking a Quest (Ambil Peta Ekspedisi)
     * Trigger: User selects a quest to execute.
     */
    public static function stakeQuest(string $userId, string $questId)
    {
        return DB::transaction(function () use ($userId, $questId) {
            // 1. Fetch user role and wallet
            $user = User::with('wallet')->findOrFail($userId);
            if ($user->role !== 'MERCHANT') {
                throw new Exception('Hanya MERCHANT yang dapat mengambil ekspedisi (Quest)');
            }

            $wallet = $user->wallet;
            if (!$wallet) {
                throw new Exception('Dompet user belum terinisialisasi');
            }

            // 2. Fetch Quest details
            $quest = Quest::findOrFail($questId);
            if (!$quest->is_active) {
                throw new Exception('Quest sedang tidak aktif');
            }

            // 3. Validation: wallets.silver_balance >= quests.stake_amount_silver
            if ($wallet->silver_balance < $quest->stake_amount_silver) {
                throw new Exception("Saldo koin perak tidak cukup. Dibutuhkan: {$quest->stake_amount_silver}, dimiliki: {$wallet->silver_balance}");
            }

            // 4. Action: Deduct stake_amount_silver from wallets.silver_balance
            $wallet->decrement('silver_balance', $quest->stake_amount_silver);

            // 5. Insert row into escrow_transactions with status LOCKED
            $expiresAt = Carbon::now()->addHours($quest->time_limit_hours);
            $escrow = EscrowTransaction::create([
                'user_id' => $userId,
                'quest_id' => $questId,
                'staked_silver' => $quest->stake_amount_silver,
                'status' => 'LOCKED',
                'expires_at' => $expiresAt,
            ]);

            return [
                'wallet' => $wallet->fresh(),
                'escrow' => $escrow,
            ];
        });
    }

    /**
     * Logic 3: Quest Resolution (Success & Gas Fee Burn)
     * Trigger: Admin/System validates quest is done before expires_at.
     */
    public static function resolveQuestSuccess(string $escrowId)
    {
        return DB::transaction(function () use ($escrowId) {
            // 1. Fetch escrow transaction
            $escrow = EscrowTransaction::with(['quest', 'user.wallet'])->findOrFail($escrowId);
            if ($escrow->status !== 'LOCKED') {
                throw new Exception("Escrow sudah diselesaikan dengan status: {$escrow->status}");
            }

            if (Carbon::now()->gt($escrow->expires_at)) {
                throw new Exception('Quest telah kedaluwarsa. Gunakan resolusi GAGAL (Logic 4)');
            }

            $user = $escrow->user;
            $quest = $escrow->quest;

            // 2. Lock and retrieve the global liquidity pool (ID = 1)
            $lp = LiquidityPoolSystem::where('id', 1)->lockForUpdate()->first();
            if (!$lp) {
                throw new Exception('Liquidity pool system not initialized');
            }

            // 3. Calculate Gas Fee: gas_fee = 5% of staked_silver
            $gasFee = intval($escrow->staked_silver * 5 / 100);
            $stakeReturn = $escrow->staked_silver - $gasFee;

            // 4. Return Stake: Add (staked_silver - gas_fee) back to user's wallets.silver_balance
            $user->wallet->increment('silver_balance', $stakeReturn);

            // 5. Give Reward: Add reward_amount_gold to user's wallets.gold_balance
            $user->wallet->increment('gold_balance', $quest->reward_amount_gold);

            // 6. Network Tax (Upline Bonus)
            $uplineBonus = 0;
            $grandUplineBonus = 0;

            // If upline_id exists: Add (25% of reward_amount_gold) to Upline's gold_balance
            if ($user->upline_id) {
                $uplineBonus = intval($quest->reward_amount_gold * 25 / 100);
                $uplineWallet = Wallet::firstOrCreate(
                    ['user_id' => $user->upline_id],
                    ['silver_balance' => 0, 'gold_balance' => 0]
                );
                $uplineWallet->increment('gold_balance', $uplineBonus);
            }

            // If grand_upline_id exists: Add (10% of reward_amount_gold) to Grand Upline's gold_balance
            if ($user->grand_upline_id) {
                $grandUplineBonus = intval($quest->reward_amount_gold * 10 / 100);
                $grandUplineWallet = Wallet::firstOrCreate(
                    ['user_id' => $user->grand_upline_id],
                    ['silver_balance' => 0, 'gold_balance' => 0]
                );
                $grandUplineWallet->increment('gold_balance', $grandUplineBonus);
            }

            // 7. Update escrow_transactions.status to SUCCESS
            $escrow->update(['status' => 'SUCCESS']);

            // 8. Update liquidity_pool_system.total_gold_in_circulation += (Reward + Upline Bonuses)
            $goldAdded = $quest->reward_amount_gold + $uplineBonus + $grandUplineBonus;
            $lp->increment('total_gold_in_circulation', $goldAdded);

            // 9. CHECK CONSTRAINT: total_gold_in_circulation MUST NOT EXCEED total_fiat_in_bank
            if ($lp->total_gold_in_circulation > $lp->total_fiat_in_bank) {
                throw new Exception(
                    "Transaksi gagal: Melanggar batas likuiditas global (Gunung Mas). Gold sirkulasi ({$lp->total_gold_in_circulation}) melebihi Fiat cadangan ({$lp->total_fiat_in_bank})."
                );
            }

            return [
                'escrow' => $escrow->fresh(),
                'user_wallet' => $user->wallet->fresh(),
                'gas_fee_burned' => $gasFee,
                'stake_returned' => $stakeReturn,
                'reward_gold' => $quest->reward_amount_gold,
                'upline_bonus' => $uplineBonus,
                'grand_upline_bonus' => $grandUplineBonus,
                'liquidity_pool' => $lp->fresh(),
            ];
        });
    }

    /**
     * Logic 4: Quest Resolution (Failed / Expired)
     * Trigger: Cron job detects NOW() > expires_at and status is LOCKED, OR manual admin fail trigger.
     */
    public static function resolveQuestFailed(string $escrowId)
    {
        return DB::transaction(function () use ($escrowId) {
            // 1. Fetch escrow transaction
            $escrow = EscrowTransaction::findOrFail($escrowId);
            if ($escrow->status !== 'LOCKED') {
                throw new Exception("Escrow sudah diselesaikan dengan status: {$escrow->status}");
            }

            // 2. Lock and retrieve global pool
            $lp = LiquidityPoolSystem::where('id', 1)->lockForUpdate()->first();
            if (!$lp) {
                throw new Exception('Liquidity pool system not initialized');
            }

            // 3. Update status to FAILED
            $escrow->update(['status' => 'FAILED']);

            // 4. Convert the lost staked_silver value 1:1 into fiat and add to liquidity_pool_system.total_fiat_in_bank
            $lp->increment('total_fiat_in_bank', $escrow->staked_silver);

            return [
                'escrow' => $escrow->fresh(),
                'absorbed_fiat' => $escrow->staked_silver,
                'liquidity_pool' => $lp->fresh(),
            ];
        });
    }

    /**
     * Logic 5: Transfer Coins (P2P Transfer)
     * Constraint: Sender must have license_status ACTIVE and at least 5 active downlines.
     */
    public static function transferCoins(string $senderId, string $targetWhatsapp, int $amount, string $coinType = 'silver')
    {
        return DB::transaction(function () use ($senderId, $targetWhatsapp, $amount, $coinType) {
            $sender = User::findOrFail($senderId);
            if ($sender->license_status !== 'ACTIVE') {
                throw new Exception("Akses Transfer Terkunci: Hanya pengguna dengan Lisensi Aktif yang dapat mentransfer koin.");
            }

            // Count active downlines (license_status = 'ACTIVE' and upline_id = $senderId)
            $activeDownlinesCount = User::where('upline_id', $senderId)
                ->where('license_status', 'ACTIVE')
                ->count();

            if ($activeDownlinesCount < 5) {
                throw new Exception("Akses Transfer Terkunci: Butuh minimal 5 downline aktif untuk membuka fitur ini. Saat ini memiliki: {$activeDownlinesCount} downline aktif.");
            }

            // Find target user by WhatsApp
            $receiver = User::where('whatsapp', $targetWhatsapp)->first();
            if (!$receiver) {
                throw new Exception("Penerima dengan nomor WhatsApp {$targetWhatsapp} tidak ditemukan.");
            }

            if ($receiver->id === $senderId) {
                throw new Exception("Anda tidak bisa mentransfer koin ke diri sendiri.");
            }

            // Lock wallets
            $senderWallet = Wallet::where('user_id', $senderId)->lockForUpdate()->first();
            $receiverWallet = Wallet::where('user_id', $receiver->id)->lockForUpdate()->first();

            $balanceField = $coinType === 'gold' ? 'gold_balance' : 'silver_balance';

            if ($senderWallet->$balanceField < $amount) {
                throw new Exception("Saldo koin tidak cukup untuk melakukan transfer.");
            }

            // Execute transfer
            $senderWallet->decrement($balanceField, $amount);
            $receiverWallet->increment($balanceField, $amount);

            // Log experience point updates for trade/transfer activity
            ExperienceService::addExperience(
                $senderId,
                10,
                'TRADE',
                "Mentransfer {$amount} Koin " . ($coinType === 'gold' ? 'Emas' : 'Perak') . " ke {$receiver->name}"
            );

            return [
                'sender_wallet' => $senderWallet->fresh(),
                'receiver_wallet' => $receiverWallet->fresh(),
            ];
        });
    }
}
