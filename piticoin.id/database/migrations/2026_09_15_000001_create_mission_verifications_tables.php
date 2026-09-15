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
        // 1. Tambahkan kolom status skorsing juri pada tabel users jika belum ada
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'jury_banned_until')) {
                $table->timestamp('jury_banned_until')->nullable()->after('license_status');
            }
        });

        // 2. Tabel mission_verifications (Pengajuan bukti misi yang membutuhkan konsensus 5 juri)
        Schema::create('mission_verifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('quest_id')->nullable()->constrained('quests')->nullOnDelete();
            $table->foreignUuid('submitter_user_id')->constrained('users')->onDelete('cascade');
            $table->text('proof_image_url')->nullable();
            $table->text('proof_description');
            $table->string('mission_title')->default('Misi Ekspedisi Khusus');
            $table->unsignedBigInteger('reward_gold')->default(0);
            $table->unsignedBigInteger('reward_silver')->default(0);
            $table->boolean('is_honeypot')->default(false);
            $table->string('honeypot_correct_verdict')->default('REJECT'); // VERDICT yang seharusnya jika honeypot
            $table->string('status')->default('IN_VOTING'); // PENDING_JURY, IN_VOTING, CONSENSUS_APPROVED, CONSENSUS_REJECTED, EXPIRED
            $table->unsignedSmallInteger('valid_votes_count')->default(0);
            $table->unsignedSmallInteger('reject_votes_count')->default(0);
            $table->unsignedSmallInteger('total_jurors_count')->default(5);
            $table->timestamp('consensus_reached_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('submitter_user_id');
        });

        // 3. Tabel mission_verification_jurors (Penugasan 5 juri acak, staking pasak, dan suara voting)
        Schema::create('mission_verification_jurors', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('verification_id')->constrained('mission_verifications')->onDelete('cascade');
            $table->foreignUuid('juror_user_id')->constrained('users')->onDelete('cascade');
            $table->string('vote')->default('PENDING'); // PENDING, VALID, REJECT
            $table->unsignedInteger('stake_amount_silver')->default(50);
            $table->unsignedInteger('reward_amount_silver')->default(0);
            $table->text('notes')->nullable();
            $table->timestamp('voted_at')->nullable();
            $table->boolean('is_slashed')->default(false);
            $table->timestamps();

            $table->unique(['verification_id', 'juror_user_id']);
            $table->index(['juror_user_id', 'vote']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mission_verification_jurors');
        Schema::dropIfExists('mission_verifications');

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'jury_banned_until')) {
                $table->dropColumn('jury_banned_until');
            }
        });
    }
};
