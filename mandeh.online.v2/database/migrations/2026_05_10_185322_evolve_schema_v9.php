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
        // 1. Rename businesses to entities
        if (Schema::hasTable('businesses')) {
            Schema::rename('businesses', 'entities');
        }

        Schema::table('entities', function (Blueprint $table) {
            if (!Schema::hasColumn('entities', 'slug')) {
                $table->string('slug')->unique()->nullable()->after('name');
            }
        });

        // 2. Evolve users table
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'trust_score')) {
                $table->integer('trust_score')->default(50);
            }
            if (!Schema::hasColumn('users', 'device_fingerprint')) {
                $table->string('device_fingerprint')->nullable();
            }
        });

        // 3. Evolve interactions table
        Schema::table('interactions', function (Blueprint $table) {
            if (Schema::hasColumn('interactions', 'business_id')) {
                $table->renameColumn('business_id', 'entity_id');
            }
            if (!Schema::hasColumn('interactions', 'status')) {
                $table->string('status')->default('pending_verification'); // pending_verification, public, rejected
            }
            if (!Schema::hasColumn('interactions', 'gps_lat')) {
                $table->decimal('gps_lat', 10, 8)->nullable();
            }
            if (!Schema::hasColumn('interactions', 'gps_lng')) {
                $table->decimal('gps_lng', 11, 8)->nullable();
            }
            if (!Schema::hasColumn('interactions', 'payload_json')) {
                $table->json('payload_json')->nullable();
            }
            if (!Schema::hasColumn('interactions', 'device_fingerprint')) {
                $table->string('device_fingerprint')->nullable();
            }
        });

        // 4. Create moderation_queue
        if (!Schema::hasTable('moderation_queue')) {
            Schema::create('moderation_queue', function (Blueprint $table) {
                $table->id();
                $table->foreignId('interaction_id')->constrained('interactions')->onDelete('cascade');
                $table->foreignUuid('assigned_to')->nullable()->constrained('users')->onDelete('set null');
                $table->string('decision')->nullable(); // approved, rejected
                $table->text('reason')->nullable();
                $table->timestamp('reviewed_at')->nullable();
                $table->timestamps();
            });
        }

        // 5. Create business_meta (for Partner features)
        if (!Schema::hasTable('business_meta')) {
            Schema::create('business_meta', function (Blueprint $table) {
                $table->id();
                $table->foreignUuid('entity_id')->constrained('entities')->onDelete('cascade');
                $table->json('menu_json')->nullable();
                $table->json('promotions_json')->nullable();
                $table->json('transaction_summary')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_meta');
        Schema::dropIfExists('moderation_queue');
        
        Schema::table('interactions', function (Blueprint $table) {
            $table->renameColumn('entity_id', 'business_id');
            $table->dropColumn(['status', 'gps_lat', 'gps_lng', 'payload_json', 'device_fingerprint']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['trust_score', 'device_fingerprint']);
        });

        if (Schema::hasTable('entities')) {
            Schema::rename('entities', 'businesses');
        }
    }
};
