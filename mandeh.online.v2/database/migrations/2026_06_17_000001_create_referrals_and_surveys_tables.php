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
        // 1. Add upline_id to users
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'upline_id')) {
                $table->foreignUuid('upline_id')->nullable()->constrained('users')->onDelete('set null');
            }
        });

        // 2. Add upline_id and qr_code_path to entities
        Schema::table('entities', function (Blueprint $table) {
            if (!Schema::hasColumn('entities', 'upline_id')) {
                $table->foreignUuid('upline_id')->nullable()->constrained('users')->onDelete('set null');
            }
            if (!Schema::hasColumn('entities', 'qr_code_path')) {
                $table->string('qr_code_path')->nullable()->after('qr_token');
            }
        });

        // 3. Add is_inaugurated to vanguard_participants
        Schema::table('vanguard_participants', function (Blueprint $table) {
            if (!Schema::hasColumn('vanguard_participants', 'is_inaugurated')) {
                $table->boolean('is_inaugurated')->default(false)->after('is_vanguard_graduated');
            }
        });

        // 4. Create entity_associations table
        if (!Schema::hasTable('entity_associations')) {
            Schema::create('entity_associations', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('entity_id')->constrained('entities')->onDelete('cascade');
                $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
                $table->string('role_in_business'); // driver, manager, staff
                $table->timestamps();
            });
        }

        // 5. Create vanguard_surveys table
        if (!Schema::hasTable('vanguard_surveys')) {
            Schema::create('vanguard_surveys', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('title');
                $table->text('description')->nullable();
                $table->json('target_roles')->nullable(); // e.g. ['vanguard', 'warga']
                $table->timestamp('start_time')->nullable();
                $table->timestamp('end_time')->nullable();
                $table->timestamps();
            });
        }

        // 6. Add survey_id to vanguard_votes
        Schema::table('vanguard_votes', function (Blueprint $table) {
            if (!Schema::hasColumn('vanguard_votes', 'survey_id')) {
                $table->foreignUuid('survey_id')->nullable()->constrained('vanguard_surveys')->onDelete('cascade');
            }
        });

        // 7. Create vanguard_vote_participations table
        if (!Schema::hasTable('vanguard_vote_participations')) {
            Schema::create('vanguard_vote_participations', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('survey_id')->constrained('vanguard_surveys')->onDelete('cascade');
                $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
                $table->timestamp('voted_at')->nullable();
                $table->timestamps();
                
                $table->unique(['survey_id', 'user_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vanguard_vote_participations');

        Schema::table('vanguard_votes', function (Blueprint $table) {
            if (Schema::hasColumn('vanguard_votes', 'survey_id')) {
                $table->dropForeign(['survey_id']);
                $table->dropColumn('survey_id');
            }
        });

        Schema::dropIfExists('vanguard_surveys');
        Schema::dropIfExists('entity_associations');

        Schema::table('vanguard_participants', function (Blueprint $table) {
            if (Schema::hasColumn('vanguard_participants', 'is_inaugurated')) {
                $table->dropColumn('is_inaugurated');
            }
        });

        Schema::table('entities', function (Blueprint $table) {
            if (Schema::hasColumn('entities', 'upline_id')) {
                $table->dropForeign(['upline_id']);
                $table->dropColumn('upline_id');
            }
            if (Schema::hasColumn('entities', 'qr_code_path')) {
                $table->dropColumn('qr_code_path');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'upline_id')) {
                $table->dropForeign(['upline_id']);
                $table->dropColumn('upline_id');
            }
        });
    }
};
