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
            if (!Schema::hasColumn('users', 'persona')) {
                $table->string('persona', 50)->default('UMKM')->after('role_class'); // UMKM, PEMUDA, GURU, PELAJAR
            }
            if (!Schema::hasColumn('users', 'subscription_tier')) {
                $table->string('subscription_tier', 50)->default('FREE')->after('license_status');
            }
        });

        Schema::table('quests', function (Blueprint $table) {
            if (!Schema::hasColumn('quests', 'quest_category')) {
                $table->string('quest_category', 50)->default('PERSONA_SPECIFIC')->after('description'); // DEFAULT, TIME_WINDOW, PERSONA_SPECIFIC
            }
            if (!Schema::hasColumn('quests', 'target_persona')) {
                $table->string('target_persona', 50)->default('ALL')->after('quest_category'); // ALL, UMKM, PEMUDA, GURU, PELAJAR
            }
            if (!Schema::hasColumn('quests', 'time_window')) {
                $table->string('time_window', 50)->default('ALL')->after('target_persona'); // ALL, MORNING, AFTERNOON, EVENING
            }
            if (!Schema::hasColumn('quests', 'min_level')) {
                $table->integer('min_level')->default(1)->after('time_window');
            }
            if (!Schema::hasColumn('quests', 'parameters')) {
                $table->json('parameters')->nullable()->after('min_level');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['persona', 'subscription_tier']);
        });

        Schema::table('quests', function (Blueprint $table) {
            $table->dropColumn(['quest_category', 'target_persona', 'time_window', 'min_level', 'parameters']);
        });
    }
};
