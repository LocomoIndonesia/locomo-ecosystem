<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('learners', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('display_name', 80); // Hanya nama panggilan
            $table->smallInteger('grade_level')->default(3);
            $table->smallInteger('birth_year')->nullable();
            $table->string('avatar_id', 40)->default('avatar_kancil_01');
            $table->boolean('is_synthetic')->default(false); // Penanda data uji simulasi
            $table->uuid('created_by');
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('created_by')->references('id')->on('users');
        });

        Schema::create('enrollments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('program_id');
            $table->uuid('learner_id');
            $table->string('status', 20)->default('active'); // pending_guardian, active, paused, left
            $table->timestamp('enrolled_at')->useCurrent();
            $table->timestamp('left_at')->nullable();

            $table->unique(['program_id', 'learner_id']);
            $table->foreign('program_id')->references('id')->on('learning_programs')->onDelete('cascade');
            $table->foreign('learner_id')->references('id')->on('learners')->onDelete('cascade');
        });

        Schema::create('guardian_relations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('learner_id');
            $table->uuid('guardian_user_id')->nullable(); // Terisi saat klaim undangan
            $table->string('relation_type', 30)->default('parent'); // parent, legal_guardian, caregiver
            $table->string('status', 20)->default('invited'); // invited, claimed, verified, revoked
            $table->string('verified_via', 40)->nullable(); // two_sided_educator_confirmation
            $table->uuid('educator_confirmed_by')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('learner_id')->references('id')->on('learners')->onDelete('cascade');
            $table->foreign('guardian_user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('educator_confirmed_by')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('guardian_invitations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('guardian_relation_id');
            $table->uuid('program_id');
            $table->char('token_hash', 64)->unique(); // SHA-256
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->uuid('created_by');

            $table->foreign('guardian_relation_id')->references('id')->on('guardian_relations')->onDelete('cascade');
            $table->foreign('program_id')->references('id')->on('learning_programs')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users');
        });

        Schema::create('consents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('guardian_relation_id');
            $table->uuid('program_id');
            $table->string('purpose_scope', 60); // PROGRAM_PARTICIPATION, PROGRESS_REPORTS
            $table->string('policy_version', 20);
            $table->timestamp('granted_at')->useCurrent();
            $table->timestamp('revoked_at')->nullable();

            $table->foreign('guardian_relation_id')->references('id')->on('guardian_relations')->onDelete('cascade');
            $table->foreign('program_id')->references('id')->on('learning_programs')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consents');
        Schema::dropIfExists('guardian_invitations');
        Schema::dropIfExists('guardian_relations');
        Schema::dropIfExists('enrollments');
        Schema::dropIfExists('learners');
    }
};
