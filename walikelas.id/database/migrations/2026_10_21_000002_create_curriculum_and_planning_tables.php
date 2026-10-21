<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('competencies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 50); // e.g. LIT-B-EXP-01
            $table->string('domain', 40)->default('literacy');
            $table->string('title', 200);
            $table->text('description');
            $table->string('grade_band', 20)->default('FASE_B_GR3');
            $table->uuid('prerequisite_id')->nullable();
            $table->smallInteger('version')->default(1);
            $table->string('review_status', 20)->default('reviewed'); // draft, reviewed, retired
            $table->text('source_ref')->nullable(); // kurikulum acuan
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['code', 'version']);
            $table->foreign('prerequisite_id')->references('id')->on('competencies')->nullOnDelete();
        });

        Schema::create('rubrics', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('competency_id');
            $table->smallInteger('version')->default(1);
            $table->jsonb('levels'); // JSON criteria: [NEEDS_SUPPORT, DEVELOPING, INDICATED_MASTERY]
            $table->string('origin', 30)->default('curated'); // curated, educator_authored
            $table->string('review_status', 20)->default('reviewed');
            $table->uuid('created_by');
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['competency_id', 'version']);
            $table->foreign('competency_id')->references('id')->on('competencies')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users');
        });

        Schema::create('learning_plans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('program_id')->unique();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('program_id')->references('id')->on('learning_programs')->onDelete('cascade');
        });

        Schema::create('plan_versions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('plan_id');
            $table->smallInteger('version_no');
            $table->string('status', 20)->default('draft'); // draft, ready_for_review, approved, superseded
            $table->string('source_type', 20); // import, wizard, template
            $table->jsonb('content'); // {objectives[], activities[], assessment_strategy}
            $table->jsonb('ambiguities')->default('[]'); // Field ambigu/perlu konfirmasi (BP §7.5)
            $table->uuid('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->uuid('created_by');
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['plan_id', 'version_no']);
            $table->foreign('plan_id')->references('id')->on('learning_plans')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users');
        });

        Schema::create('plan_sources', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('plan_version_id');
            $table->string('kind', 20)->default('pasted_text'); // pasted_text, file_upload
            $table->text('raw_text');
            $table->jsonb('extracted')->nullable(); // Output ekstraksi Gemini Intent Compiler
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('plan_version_id')->references('id')->on('plan_versions')->onDelete('cascade');
        });

        // Add foreign key from learning_programs to plan_versions
        Schema::table('learning_programs', function (Blueprint $table) {
            $table->foreign('active_plan_version_id')->references('id')->on('plan_versions')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('learning_programs', function (Blueprint $table) {
            $table->dropForeign(['active_plan_version_id']);
        });

        Schema::dropIfExists('plan_sources');
        Schema::dropIfExists('plan_versions');
        Schema::dropIfExists('learning_plans');
        Schema::dropIfExists('rubrics');
        Schema::dropIfExists('competencies');
    }
};
