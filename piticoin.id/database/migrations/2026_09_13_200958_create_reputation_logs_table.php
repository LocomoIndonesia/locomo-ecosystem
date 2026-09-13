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
        Schema::create('reputation_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('giver_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('receiver_id')->constrained('users')->cascadeOnDelete();
            $table->string('action_type'); // ENDORSE, TRX_SUCCESS, MISSION_SUCCESS, SLASHING
            $table->integer('point_change');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reputation_logs');
    }
};
