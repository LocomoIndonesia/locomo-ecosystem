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
        Schema::create('user_reputations', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->integer('base_score')->default(420);
            $table->integer('network_score')->default(0);
            $table->integer('transaction_score')->default(0);
            $table->integer('checkin_score')->default(0);
            $table->integer('mission_score')->default(0);
            $table->integer('total_score')->default(420);
            $table->enum('current_status', [
                'DIASINGKAN', 
                'DIBENCI', 
                'DIKENAL', 
                'DISUKAI', 
                'DIHORMATI', 
                'DICINTAI'
            ])->default('DIKENAL');
            $table->boolean('is_suspended')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_reputations');
    }
};
