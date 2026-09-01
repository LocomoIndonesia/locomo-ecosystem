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
            if (!Schema::hasColumn('vanguard_participants', 'gender')) {
                $table->string('gender', 10)->nullable()->after('name');
            }
            if (!Schema::hasColumn('vanguard_participants', 'date_of_birth')) {
                $table->date('date_of_birth')->nullable()->after('gender');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vanguard_participants', function (Blueprint $table) {
            $table->dropColumn(['gender', 'date_of_birth']);
        });
    }
};
