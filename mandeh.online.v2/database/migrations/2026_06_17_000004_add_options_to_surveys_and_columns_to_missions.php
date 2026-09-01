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
        Schema::table('vanguard_surveys', function (Blueprint $table) {
            if (!Schema::hasColumn('vanguard_surveys', 'options')) {
                $table->json('options')->nullable()->after('target_roles');
            }
        });

        Schema::table('vanguard_votes', function (Blueprint $table) {
            if (!Schema::hasColumn('vanguard_votes', 'voter_hash')) {
                $table->string('voter_hash')->nullable()->after('survey_id');
            }
        });

        Schema::table('vanguard_missions', function (Blueprint $table) {
            if (!Schema::hasColumn('vanguard_missions', 'start_time')) {
                $table->timestamp('start_time')->nullable();
            }
            if (!Schema::hasColumn('vanguard_missions', 'end_time')) {
                $table->timestamp('end_time')->nullable();
            }
            if (!Schema::hasColumn('vanguard_missions', 'bonus_points')) {
                $table->integer('bonus_points')->default(0);
            }
            if (!Schema::hasColumn('vanguard_missions', 'badge_id')) {
                $table->string('badge_id')->nullable();
            }
            if (!Schema::hasColumn('vanguard_missions', 'admin_id')) {
                $table->foreignUuid('admin_id')->nullable()->constrained('users')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vanguard_missions', function (Blueprint $table) {
            if (Schema::hasColumn('vanguard_missions', 'admin_id')) {
                $table->dropForeign(['admin_id']);
                $table->dropColumn('admin_id');
            }
            if (Schema::hasColumn('vanguard_missions', 'badge_id')) {
                $table->dropColumn('badge_id');
            }
            if (Schema::hasColumn('vanguard_missions', 'bonus_points')) {
                $table->dropColumn('bonus_points');
            }
            if (Schema::hasColumn('vanguard_missions', 'end_time')) {
                $table->dropColumn('end_time');
            }
            if (Schema::hasColumn('vanguard_missions', 'start_time')) {
                $table->dropColumn('start_time');
            }
        });

        Schema::table('vanguard_votes', function (Blueprint $table) {
            if (Schema::hasColumn('vanguard_votes', 'voter_hash')) {
                $table->dropColumn('voter_hash');
            }
        });

        Schema::table('vanguard_surveys', function (Blueprint $table) {
            if (Schema::hasColumn('vanguard_surveys', 'options')) {
                $table->dropColumn('options');
            }
        });
    }
};
