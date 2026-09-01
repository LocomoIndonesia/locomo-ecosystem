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
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('processed_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('processed_by_name')->nullable();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('processed_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('processed_by_name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['processed_by_user_id']);
            $table->dropColumn(['processed_by_user_id', 'processed_by_name']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['processed_by_user_id']);
            $table->dropColumn(['processed_by_user_id', 'processed_by_name']);
        });
    }
};
