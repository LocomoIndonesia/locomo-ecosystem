<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('agent_missions', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('user_id')->constrained('users'); // ID Agen
            $table->string('mission_type')->default('unimart_registration');
            $table->enum('status', ['active', 'completed', 'released', 'expired'])->default('active');
            $table->decimal('staking_amount', 12, 2)->default(2500.00); // Perak terkunci
            $table->decimal('reward_gold_amount', 12, 2)->nullable(); // Emas (25% dari tagihan bulanan Unimart)
            $table->string('business_name');
            $table->string('owner_email')->index();
            $table->string('owner_phone');
            $table->timestamp('staking_locked_at');
            $table->timestamp('expires_at'); // staking_locked_at + 24 jam
            $table->timestamp('confirmed_at')->nullable(); // Konfirmasi email/WA
            $table->timestamp('released_at')->nullable(); // Pembayaran pertama sukses
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agent_missions');
    }
};
