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
        Schema::table('users', function (Blueprint $table) {
            $table->string('faction')->nullable()->after('role'); // NISNAS, HUMAN, ANDROID
            $table->string('role_class')->nullable()->after('faction'); // PENDEKAR, RANGER, SPESIALIS, MENTALIST
            $table->string('license_type')->default('NONE')->after('role_class'); // NONE, PEMULA, PETUALANG, SAUDAGAR
            $table->string('license_status')->default('INACTIVE')->after('license_type'); // INACTIVE, ACTIVE
            $table->integer('onboarding_step')->default(1)->after('license_status'); // 1 to 6 (6 = completed)
            $table->integer('level')->default(1)->after('onboarding_step');
            $table->integer('experience_points')->default(0)->after('level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'faction',
                'role_class',
                'license_type',
                'license_status',
                'onboarding_step',
                'level',
                'experience_points',
            ]);
        });
    }
};
