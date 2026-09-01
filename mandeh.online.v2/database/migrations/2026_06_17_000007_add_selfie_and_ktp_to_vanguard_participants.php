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
            if (!Schema::hasColumn('vanguard_participants', 'selfie_path')) {
                $table->string('selfie_path')->nullable()->after('join_reason');
            }
            if (!Schema::hasColumn('vanguard_participants', 'ktp_path')) {
                $table->string('ktp_path')->nullable()->after('selfie_path');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vanguard_participants', function (Blueprint $table) {
            $table->dropColumn(['selfie_path', 'ktp_path']);
        });
    }
};
