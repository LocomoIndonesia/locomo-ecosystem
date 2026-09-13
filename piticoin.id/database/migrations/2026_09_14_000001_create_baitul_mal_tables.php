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
        // 1. Kas Baitul Mal (Pool Kas Terpusat Transparan)
        Schema::create('baitul_mal_pools', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('Kas Sosial Baitul Mal Gunung Mas');
            $table->unsignedBigInteger('total_collected_gold')->default(0);
            $table->unsignedBigInteger('active_balance_gold')->default(0);
            $table->unsignedBigInteger('total_distributed_gold')->default(0);
            $table->unsignedInteger('total_donations_count')->default(0);
            $table->unsignedInteger('total_beneficiaries_count')->default(0);
            $table->timestamps();
        });

        // Seed initial pool record jika belum ada
        DB::table('baitul_mal_pools')->insert([
            'name' => 'Kas Sosial Baitul Mal PitiCoin & Gunung Mas',
            'total_collected_gold' => 150000,
            'active_balance_gold' => 65000,
            'total_distributed_gold' => 85000,
            'total_donations_count' => 28,
            'total_beneficiaries_count' => 17,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. Periode Distribusi Santunan (Batch Distribution)
        Schema::create('baitul_mal_periods', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('period_name');
            $table->timestamp('distribution_date');
            $table->unsignedBigInteger('total_gold_distributed')->default(0);
            $table->unsignedInteger('beneficiaries_count')->default(0);
            $table->string('status')->default('DISTRIBUTED'); // DRAFT, OPEN_AUDIT, DISTRIBUTED
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 3. Riwayat Donasi (Sedekah Masuk dengan Masking Privasi)
        Schema::create('baitul_mal_donations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->nullable();
            $table->unsignedBigInteger('amount_gold');
            $table->string('masked_donor_name');
            $table->string('masked_donor_phone')->nullable();
            $table->text('prayer_message')->nullable();
            $table->boolean('is_anonymous')->default(false);
            $table->unsignedInteger('aamiin_count')->default(0);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });

        // 4. Riwayat Penerima Manfaat per Periode (Mustahik)
        Schema::create('baitul_mal_distributions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('period_id');
            $table->uuid('beneficiary_user_id')->nullable();
            $table->string('masked_beneficiary_name');
            $table->string('beneficiary_category'); // PENJELAJAH_PEMULA, DHUAFA_MITRA, DANA_DARURAT
            $table->unsignedBigInteger('amount_gold');
            $table->text('distribution_note')->nullable();
            $table->timestamp('disbursed_at');
            $table->timestamps();

            $table->foreign('period_id')->references('id')->on('baitul_mal_periods')->onDelete('cascade');
            $table->foreign('beneficiary_user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('baitul_mal_distributions');
        Schema::dropIfExists('baitul_mal_donations');
        Schema::dropIfExists('baitul_mal_periods');
        Schema::dropIfExists('baitul_mal_pools');
    }
};
