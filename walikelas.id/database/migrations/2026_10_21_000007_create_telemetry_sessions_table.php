<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('telemetry_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('program_id')->nullable();
            $table->string('action_type', 50); // triage_review, rubric_scoring, plan_generation, report_publishing
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->integer('duration_seconds')->nullable();
            $table->integer('baseline_seconds')->default(900); // 15 menit waktu cara konvensional
            $table->integer('net_seconds_saved')->nullable();
            $table->jsonb('metadata')->default('{}');
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('program_id')->references('id')->on('learning_programs')->nullOnDelete();
            $table->index(['user_id', 'action_type']);
            $table->index(['program_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('telemetry_sessions');
    }
};
