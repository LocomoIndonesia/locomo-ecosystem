<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ledgers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('reference_type')->nullable(); // e.g., App\Models\Quest, App\Models\PpobTransaction
            $table->string('reference_id')->nullable();
            $table->string('description');
            $table->timestamps();
        });

        Schema::create('ledger_entries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('ledger_id');
            $table->uuid('wallet_id')->nullable(); // nullable indicates a system vault (e.g. Burn Vault)
            $table->string('currency'); // FIAT, GOLD, SILVER
            $table->string('type'); // DEBIT, CREDIT
            $table->decimal('amount', 16, 2)->default(0); // Decimal to handle both fiat (Rp) and coins safely
            $table->timestamps();

            $table->foreign('ledger_id')->references('id')->on('ledgers')->onDelete('cascade');
            // If wallet_id is a user's wallet, it references users(id). If null, it's a system account.
            $table->foreign('wallet_id')->references('id')->on('users')->onDelete('cascade');
        });

        // Ensure amount is strictly positive to prevent ledger manipulation
        DB::statement('ALTER TABLE ledger_entries ADD CONSTRAINT check_ledger_amount_positive CHECK (amount > 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('ledger_entries');
        Schema::dropIfExists('ledgers');
    }
};
