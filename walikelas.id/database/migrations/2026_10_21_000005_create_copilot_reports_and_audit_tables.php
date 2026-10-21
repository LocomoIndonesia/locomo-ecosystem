<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_runs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('purpose', 40); // plan_extract, gap_analysis, intervention, report_draft
            $table->uuid('program_id')->nullable();
            $table->string('prompt_version', 30);
            $table->string('model_name', 80); // gemini-1.5-flash, gemini-2.0-flash
            $table->jsonb('input_refs'); // Non-PII reference pointers
            $table->jsonb('output_payload')->nullable();
            $table->string('status', 20); // ok, schema_invalid, timeout, refused, error
            $table->integer('latency_ms')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('program_id')->references('id')->on('learning_programs')->nullOnDelete();
        });

        Schema::create('ai_suggestions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('ai_run_id');
            $table->uuid('program_id');
            $table->uuid('enrollment_id')->nullable();
            $table->string('kind', 30); // plan_draft, intervention, report_draft
            $table->jsonb('payload');
            $table->text('rationale');
            $table->string('uncertainty', 25); // low, medium, high, insufficient_evidence
            $table->string('status', 20)->default('pending'); // pending, accepted, edited, rejected
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('ai_run_id')->references('id')->on('ai_runs')->onDelete('cascade');
            $table->foreign('program_id')->references('id')->on('learning_programs')->onDelete('cascade');
            $table->foreign('enrollment_id')->references('id')->on('enrollments')->onDelete('cascade');
        });

        Schema::create('educator_decisions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('suggestion_id')->nullable();
            $table->string('subject_type', 40); // plan_version, intervention, report, assignment
            $table->uuid('subject_id');
            $table->string('decision', 20); // approve, edit, reject
            $table->text('reason')->nullable();
            $table->jsonb('edited_payload')->nullable();
            $table->uuid('decided_by');
            $table->timestamp('decided_at')->useCurrent();

            $table->foreign('suggestion_id')->references('id')->on('ai_suggestions')->nullOnDelete();
            $table->foreign('decided_by')->references('id')->on('users');
        });

        Schema::create('interventions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('program_id');
            $table->uuid('enrollment_id')->nullable();
            $table->uuid('competency_id');
            $table->text('description');
            $table->uuid('decision_id');
            $table->string('status', 20)->default('planned'); // planned, done, cancelled
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('program_id')->references('id')->on('learning_programs')->onDelete('cascade');
            $table->foreign('enrollment_id')->references('id')->on('enrollments')->onDelete('cascade');
            $table->foreign('competency_id')->references('id')->on('competencies')->onDelete('cascade');
            $table->foreign('decision_id')->references('id')->on('educator_decisions')->onDelete('cascade');
        });

        Schema::create('reports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('program_id');
            $table->uuid('enrollment_id')->nullable(); // NULL = group/cohort aggregate report
            $table->smallInteger('version_no')->default(1);
            $table->string('status', 20)->default('draft'); // draft, approved, needs_review, superseded
            $table->jsonb('facts_layer'); // attendance, completion counts
            $table->jsonb('findings_layer'); // rubrics & competency states
            $table->jsonb('recommendations_layer'); // next activity recommendations
            $table->jsonb('attribution_tags'); // human_note vs auto_assessment vs educator_validated
            $table->uuid('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('program_id')->references('id')->on('learning_programs')->onDelete('cascade');
            $table->foreign('enrollment_id')->references('id')->on('enrollments')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('report_sources', function (Blueprint $table) {
            $table->uuid('report_id');
            $table->string('source_type', 40); // responses, mastery_states, educator_observations
            $table->uuid('source_id');

            $table->primary(['report_id', 'source_type', 'source_id']);
            $table->foreign('report_id')->references('id')->on('reports')->onDelete('cascade');
        });

        Schema::create('outbox_messages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('guardian_relation_id');
            $table->uuid('program_id');
            $table->string('channel', 20)->default('web_link'); // web_link, email, whatsapp
            $table->string('template_key', 60);
            $table->string('status', 25)->default('queued'); // queued, held_quiet_hours, blocked_policy, link_ready, shared_manually, sent, failed
            $table->jsonb('policy_check'); // consent & quiet hours check logs
            $table->timestamp('scheduled_for')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('guardian_relation_id')->references('id')->on('guardian_relations')->onDelete('cascade');
            $table->foreign('program_id')->references('id')->on('learning_programs')->onDelete('cascade');
        });

        Schema::create('audit_events', function (Blueprint $table) {
            $table->id(); // BIGSERIAL append-only (TI-13)
            $table->string('actor_type', 20); // user, system, guardian_link
            $table->uuid('actor_id')->nullable();
            $table->string('action', 80);
            $table->string('subject_type', 40)->nullable();
            $table->uuid('subject_id')->nullable();
            $table->uuid('program_id')->nullable();
            $table->jsonb('payload')->default('{}');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['program_id', 'created_at']);
            $table->index(['actor_type', 'actor_id']);
            $table->index(['action', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_events');
        Schema::dropIfExists('outbox_messages');
        Schema::dropIfExists('report_sources');
        Schema::dropIfExists('reports');
        Schema::dropIfExists('interventions');
        Schema::dropIfExists('educator_decisions');
        Schema::dropIfExists('ai_suggestions');
        Schema::dropIfExists('ai_runs');
    }
};
