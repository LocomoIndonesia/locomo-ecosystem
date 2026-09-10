<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->unique()->after('name');
            $table->decimal('rating', 3, 1)->default(5.0)->after('experience_points');
            $table->integer('total_reviews')->default(1)->after('rating');
            $table->boolean('is_root')->default(false)->after('role');
        });

        Schema::create('consultations', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignUuid('sender_id')->constrained('users')->onDelete('cascade');
            $table->string('sender_role')->default('ADMIN'); // ADMIN, SAUDAGAR
            $table->string('subject')->nullable();
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consultations');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['username', 'rating', 'total_reviews', 'is_root']);
        });
    }
};
