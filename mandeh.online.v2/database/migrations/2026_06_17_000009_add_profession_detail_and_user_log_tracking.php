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
            if (!Schema::hasColumn('vanguard_participants', 'profession_detail')) {
                $table->string('profession_detail')->nullable()->after('profession');
            }
        });

        if (!Schema::hasTable('user_log_tracking')) {
            Schema::create('user_log_tracking', function (Blueprint $table) {
                $table->id();
                $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
                $table->double('latitude');
                $table->double('longitude');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_log_tracking');
        Schema::table('vanguard_participants', function (Blueprint $table) {
            if (Schema::hasColumn('vanguard_participants', 'profession_detail')) {
                $table->dropColumn('profession_detail');
            }
        });
    }
};
