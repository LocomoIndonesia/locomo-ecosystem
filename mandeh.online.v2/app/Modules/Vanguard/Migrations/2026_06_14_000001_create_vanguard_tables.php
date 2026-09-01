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
        Schema::create('vanguard_participants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
            $table->string('tracking_id')->unique();
            $table->string('name');
            $table->enum('origin', ['lokal', 'perantau']);
            $table->enum('armada', ['darat_laut', 'udara']);
            $table->integer('reputation_points')->default(0);
            $table->boolean('is_vanguard_graduated')->default(false);
            $table->timestamp('voted_at')->nullable();
            $table->timestamps();
        });

        Schema::create('vanguard_checkins', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('participant_id')->constrained('vanguard_participants')->onDelete('cascade');
            $table->date('checkin_date');
            $table->enum('verified_method', ['face_to_face', 'livestream']);
            $table->timestamps();
            
            $table->unique(['participant_id', 'checkin_date']);
        });

        Schema::create('vanguard_missions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->text('description');
            $table->enum('armada_type', ['darat_laut', 'udara', 'both']);
            $table->integer('points_reward')->default(10);
            $table->timestamps();
        });

        Schema::create('vanguard_mission_reports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('participant_id')->constrained('vanguard_participants')->onDelete('cascade');
            $table->foreignUuid('mission_id')->constrained('vanguard_missions')->onDelete('cascade');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('report_text');
            $table->string('proof_url')->nullable();
            $table->integer('points_awarded')->default(0);
            $table->timestamps();
        });

        Schema::create('vanguard_votes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->text('encrypted_ballot');
            $table->string('signature_hash'); // Strong SHA-256 integrity hash
            $table->timestamps();
        });

        Schema::create('tic_revitalization_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->string('reporter_name');
            $table->text('description');
            $table->enum('status', ['pending', 'in_progress', 'completed'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tic_revitalization_logs');
        Schema::dropIfExists('vanguard_votes');
        Schema::dropIfExists('vanguard_mission_reports');
        Schema::dropIfExists('vanguard_missions');
        Schema::dropIfExists('vanguard_checkins');
        Schema::dropIfExists('vanguard_participants');
    }
};
