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
            if (!Schema::hasColumn('vanguard_participants', 'nagari')) {
                $table->string('nagari')->nullable()->after('initial_rating');
            }
            if (!Schema::hasColumn('vanguard_participants', 'kecamatan')) {
                $table->string('kecamatan')->nullable()->after('nagari');
            }
            if (!Schema::hasColumn('vanguard_participants', 'kabupaten')) {
                $table->string('kabupaten')->nullable()->after('kecamatan');
            }
            if (!Schema::hasColumn('vanguard_participants', 'provinsi')) {
                $table->string('provinsi')->nullable()->after('kabupaten');
            }
            if (!Schema::hasColumn('vanguard_participants', 'postal_code')) {
                $table->string('postal_code')->nullable()->after('provinsi');
            }
            if (!Schema::hasColumn('vanguard_participants', 'full_address')) {
                $table->text('full_address')->nullable()->after('postal_code');
            }
            if (!Schema::hasColumn('vanguard_participants', 'join_reason')) {
                $table->text('join_reason')->nullable()->after('full_address');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vanguard_participants', function (Blueprint $table) {
            $table->dropColumn([
                'nagari',
                'kecamatan',
                'kabupaten',
                'provinsi',
                'postal_code',
                'full_address',
                'join_reason'
            ]);
        });
    }
};
