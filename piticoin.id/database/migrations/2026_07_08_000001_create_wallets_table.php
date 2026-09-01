<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->uuid('user_id')->primary();
            $table->bigInteger('silver_balance')->default(0);
            $table->bigInteger('gold_balance')->default(0);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // Apply Database constraints for Non-negative balances
        DB::statement('ALTER TABLE wallets ADD CONSTRAINT check_silver_balance_non_negative CHECK (silver_balance >= 0)');
        DB::statement('ALTER TABLE wallets ADD CONSTRAINT check_gold_balance_non_negative CHECK (gold_balance >= 0)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};
