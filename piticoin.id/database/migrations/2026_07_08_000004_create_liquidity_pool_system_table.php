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
        Schema::create('liquidity_pool_system', function (Blueprint $table) {
            $table->integer('id')->primary()->default(1);
            $table->bigInteger('total_fiat_in_bank')->default(0);
            $table->bigInteger('total_gold_in_circulation')->default(0);
            $table->timestamps();
        });

        // Apply Database constraints: total_gold_in_circulation MUST NOT EXCEED total_fiat_in_bank
        DB::statement('ALTER TABLE liquidity_pool_system ADD CONSTRAINT check_gold_le_fiat CHECK (total_gold_in_circulation <= total_fiat_in_bank)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('liquidity_pool_system');
    }
};
