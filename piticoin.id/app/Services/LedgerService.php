<?php

namespace App\Services;

use App\Models\Ledger;
use App\Models\LedgerEntry;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Exception;

class LedgerService
{
    /**
     * Record a Double-Entry transaction between two wallets.
     *
     * @param string|null $debitWalletId Null for System Vault
     * @param string|null $creditWalletId Null for System Vault
     * @param float $amount
     * @param string $currency FIAT, GOLD, SILVER
     * @param string $description
     * @param string|null $referenceType
     * @param string|null $referenceId
     * @return Ledger
     * @throws Exception
     */
    public function recordTransaction(
        ?string $debitWalletId,
        ?string $creditWalletId,
        float $amount,
        string $currency,
        string $description,
        ?string $referenceType = null,
        ?string $referenceId = null
    ): Ledger {
        if ($amount <= 0) {
            throw new Exception("Transaction amount must be greater than zero.");
        }

        if ($debitWalletId === $creditWalletId) {
            throw new Exception("Debit and Credit wallets cannot be the same.");
        }

        return DB::transaction(function () use (
            $debitWalletId,
            $creditWalletId,
            $amount,
            $currency,
            $description,
            $referenceType,
            $referenceId
        ) {
            // 1. Create Ledger Header
            $ledger = Ledger::create([
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'description' => $description,
            ]);

            // 2. Create Debit Entry (Subtract from Sender)
            LedgerEntry::create([
                'ledger_id' => $ledger->id,
                'wallet_id' => $debitWalletId,
                'currency' => $currency,
                'type' => 'DEBIT',
                'amount' => $amount,
            ]);

            // 3. Create Credit Entry (Add to Receiver)
            LedgerEntry::create([
                'ledger_id' => $ledger->id,
                'wallet_id' => $creditWalletId,
                'currency' => $currency,
                'type' => 'CREDIT',
                'amount' => $amount,
            ]);

            // 4. Update Actual Wallet Balances
            $this->updateWalletBalance($debitWalletId, $currency, -$amount);
            $this->updateWalletBalance($creditWalletId, $currency, $amount);

            return $ledger;
        });
    }

    private function updateWalletBalance(?string $walletId, string $currency, float $changeAmount): void
    {
        if ($walletId === null) {
            // System Vault: Doesn't have a record in `wallets` table, we skip updating balance table.
            return;
        }

        // Lock the wallet row for update to prevent race conditions
        $wallet = Wallet::where('user_id', $walletId)->lockForUpdate()->first();

        if (!$wallet) {
            // Auto-create wallet if not exists
            $wallet = Wallet::create(['user_id' => $walletId]);
        }

        if ($currency === 'GOLD') {
            $wallet->gold_balance += $changeAmount;
            if ($wallet->gold_balance < 0) {
                throw new Exception("Insufficient GOLD balance.");
            }
        } elseif ($currency === 'SILVER') {
            $wallet->silver_balance += $changeAmount;
            if ($wallet->silver_balance < 0) {
                throw new Exception("Insufficient SILVER balance.");
            }
        } elseif ($currency === 'FIAT') {
            // If you have a fiat column, add here. For now, assuming PitiCoin wallets only store gold/silver directly, 
            // and fiat is passed through. If fiat balance is stored, add fiat_balance to wallets table.
            // Throwing exception if fiat balance tracking isn't implemented in Wallet model yet.
            // throw new Exception("FIAT balance tracking not implemented on wallet model.");
        } else {
            throw new Exception("Invalid currency type: $currency");
        }

        $wallet->save();
    }
}
