<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dynamic_component_catalog', function (Blueprint $table) {
            $table->string('component_type', 50)->primary(); // SingleChoiceCard, SequenceOrderingCard, etc.
            $table->jsonb('schema_definition'); // JSON Schema prop validation
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('missions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('program_id')->nullable(); // NULL = pustaka umum terkurasi
            $table->uuid('competency_id');
            $table->uuid('rubric_id');
            $table->string('kind', 20); // digital, home, in_class
            $table->string('title', 200);
            $table->smallInteger('estimated_minutes')->default(10);
            $table->jsonb('steps_payload'); // SDUI Steps
            $table->jsonb('offline_activity_guide')->nullable(); // Panduan rumah untuk wali
            $table->smallInteger('version')->default(1);
            $table->string('status', 20)->default('ready'); // draft, ready, retired
            $table->uuid('created_by');
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('program_id')->references('id')->on('learning_programs')->nullOnDelete();
            $table->foreign('competency_id')->references('id')->on('competencies')->onDelete('cascade');
            $table->foreign('rubric_id')->references('id')->on('rubrics')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users');
        });

        Schema::create('assignments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('program_id');
            $table->uuid('enrollment_id');
            $table->uuid('mission_id');
            $table->uuid('plan_version_id'); // TI-04: immutable plan version tracking
            $table->string('status', 20)->default('assigned'); // assigned, deferred, in_progress, needs_guardian, submitted, returned, evaluated, reviewed, closed, cancelled
            $table->boolean('requires_guardian_confirmation')->default(false);
            $table->boolean('requires_educator_review')->default(true);
            $table->timestamp('due_at')->nullable();
            $table->uuid('assigned_by');
            $table->timestamps();

            $table->foreign('program_id')->references('id')->on('learning_programs')->onDelete('cascade');
            $table->foreign('enrollment_id')->references('id')->on('enrollments')->onDelete('cascade');
            $table->foreign('mission_id')->references('id')->on('missions')->onDelete('cascade');
            $table->foreign('plan_version_id')->references('id')->on('plan_versions')->onDelete('cascade');
            $table->foreign('assigned_by')->references('id')->on('users');
        });

        Schema::create('assignment_transitions', function (Blueprint $table) {
            $table->id();
            $table->uuid('assignment_id');
            $table->string('from_status', 20)->nullable();
            $table->string('to_status', 20);
            $table->string('actor_type', 20); // educator, guardian, learner_link, system
            $table->uuid('actor_id')->nullable();
            $table->text('reason')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('assignment_id')->references('id')->on('assignments')->onDelete('cascade');
        });

        Schema::create('launch_tokens', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('assignment_id');
            $table->char('token_hash', 64)->unique();
            $table->timestamp('expires_at');
            $table->timestamp('revoked_at')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('assignment_id')->references('id')->on('assignments')->onDelete('cascade');
        });

        Schema::create('attempts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('assignment_id');
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('finished_at')->nullable();
            $table->integer('active_seconds')->default(0);
            $table->boolean('assisted_by_adult')->default(false);

            $table->foreign('assignment_id')->references('id')->on('assignments')->onDelete('cascade');
        });

        Schema::create('responses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('attempt_id');
            $table->string('step_key', 50);
            $table->jsonb('answer');
            $table->smallInteger('hints_used')->default(0);
            $table->string('auto_result', 15)->nullable(); // correct, incorrect (khusus objektif)
            $table->boolean('safety_flag')->default(false);
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('attempt_id')->references('id')->on('attempts')->onDelete('cascade');
        });

        Schema::create('guardian_confirmations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('assignment_id');
            $table->uuid('guardian_relation_id');
            $table->string('response', 20); // done, not_yet, need_help
            $table->string('note', 500)->nullable();
            $table->string('channel', 30)->default('web_link');
            $table->timestamp('confirmed_at')->useCurrent();

            $table->foreign('assignment_id')->references('id')->on('assignments')->onDelete('cascade');
            $table->foreign('guardian_relation_id')->references('id')->on('guardian_relations')->onDelete('cascade');
        });

        Schema::create('educator_observations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('program_id');
            $table->uuid('enrollment_id');
            $table->uuid('assignment_id')->nullable();
            $table->string('kind', 40); // attendance, participation, difficulty, support_given, repeat_needed
            $table->string('value', 50);
            $table->string('note', 500)->nullable();
            $table->uuid('recorded_by');
            $table->timestamp('recorded_at')->useCurrent();

            $table->foreign('program_id')->references('id')->on('learning_programs')->onDelete('cascade');
            $table->foreign('enrollment_id')->references('id')->on('enrollments')->onDelete('cascade');
            $table->foreign('assignment_id')->references('id')->on('assignments')->nullOnDelete();
            $table->foreign('recorded_by')->references('id')->on('users');
        });

        Schema::create('mastery_states', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('enrollment_id');
            $table->uuid('competency_id');
            $table->string('level', 30); // no_evidence, needs_support, developing, indicated_mastery, educator_validated
            $table->jsonb('basis_refs');
            $table->string('decided_by_type', 20); // system, educator
            $table->uuid('decided_by')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('enrollment_id')->references('id')->on('enrollments')->onDelete('cascade');
            $table->foreign('competency_id')->references('id')->on('competencies')->onDelete('cascade');
            $table->foreign('decided_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mastery_states');
        Schema::dropIfExists('educator_observations');
        Schema::dropIfExists('guardian_confirmations');
        Schema::dropIfExists('responses');
        Schema::dropIfExists('attempts');
        Schema::dropIfExists('launch_tokens');
        Schema::dropIfExists('assignment_transitions');
        Schema::dropIfExists('assignments');
        Schema::dropIfExists('missions');
        Schema::dropIfExists('dynamic_component_catalog');
    }
};
