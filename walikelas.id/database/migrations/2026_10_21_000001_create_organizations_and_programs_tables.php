<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('educator_profiles', function (Blueprint $table) {
            $table->uuid('user_id')->primary();
            $table->string('display_name', 150);
            $table->text('bio')->nullable();
            $table->string('accepted_ai_terms_version', 20);
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('organizations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('kind', 30); // school, institution
            $table->string('name', 200);
            $table->string('npsn', 20)->nullable(); // Optional for private tutors (TI-01)
            $table->uuid('created_by');
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('created_by')->references('id')->on('users');
        });

        Schema::create('learning_programs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('mode', 20); // school, private
            $table->uuid('organization_id')->nullable(); // Nullable (TI-01)
            $table->string('title', 200);
            $table->text('goal_summary')->nullable();
            $table->smallInteger('grade_level')->default(3);
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->smallInteger('planned_session_count')->nullable();
            $table->string('status', 20)->default('draft'); // draft, ready_for_review, active, paused, completed, archived
            $table->uuid('active_plan_version_id')->nullable(); // FK to plan_versions added in subsequent migration
            $table->uuid('created_by');
            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users');
        });

        Schema::create('program_educators', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('program_id');
            $table->uuid('user_id');
            $table->string('role', 20)->default('lead'); // lead, collaborator
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('ended_at')->nullable();

            $table->foreign('program_id')->references('id')->on('learning_programs')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['program_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('program_educators');
        Schema::dropIfExists('learning_programs');
        Schema::dropIfExists('organizations');
        Schema::dropIfExists('educator_profiles');
    }
};
