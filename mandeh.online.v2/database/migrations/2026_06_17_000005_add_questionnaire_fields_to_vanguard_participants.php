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
        Schema::table('vanguard_participants', function (Blueprint $table) {
            if (!Schema::hasColumn('vanguard_participants', 'profile_type')) {
                $table->string('profile_type')->default('warga')->after('armada'); // warga, pelaku_wisata
            }
            if (!Schema::hasColumn('vanguard_participants', 'is_hpi_candidate')) {
                $table->boolean('is_hpi_candidate')->default(true)->after('profile_type');
            }
            if (!Schema::hasColumn('vanguard_participants', 'profession')) {
                $table->string('profession')->nullable()->after('is_hpi_candidate');
            }
            if (!Schema::hasColumn('vanguard_participants', 'initial_rating')) {
                $table->integer('initial_rating')->default(1)->after('profession');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vanguard_participants', function (Blueprint $table) {
            $table->dropColumn(['profile_type', 'is_hpi_candidate', 'profession', 'initial_rating']);
        });
    }
};
