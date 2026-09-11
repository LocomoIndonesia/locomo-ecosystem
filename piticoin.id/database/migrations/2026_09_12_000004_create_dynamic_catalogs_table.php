<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Ecosystem Services
        Schema::create('ecosystem_services', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('webhook_url')->nullable();
            $table->string('status')->default('ACTIVE'); // ACTIVE, INACTIVE
            $table->timestamps();
        });

        // 2. PPOB Categories & Products
        Schema::create('ppob_categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('ppob_products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('category_id');
            $table->string('code')->unique(); // E.g., TSEL50
            $table->string('name');
            $table->decimal('base_price', 12, 2)->default(0); // Harga modal H2H
            $table->decimal('selling_price_fiat', 12, 2)->default(0);
            $table->bigInteger('selling_price_gold')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('category_id')->references('id')->on('ppob_categories')->onDelete('cascade');
        });

        // 3. Inventory Items (Game/Gamification)
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('type'); // BOOSTER, UNLOCKER, FREEZE
            $table->string('currency_type')->default('GOLD'); // GOLD, SILVER, FIAT
            $table->bigInteger('price')->default(0);
            $table->jsonb('effects_payload')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 4. Quest Templates
        Schema::create('quest_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('trigger_type')->default('MANUAL'); // API, MANUAL, GPS
            $table->string('currency_reward')->default('GOLD');
            $table->bigInteger('reward_amount')->default(0);
            $table->bigInteger('stake_amount_silver')->default(0);
            $table->jsonb('parameters')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quest_templates');
        Schema::dropIfExists('inventory_items');
        Schema::dropIfExists('ppob_products');
        Schema::dropIfExists('ppob_categories');
        Schema::dropIfExists('ecosystem_services');
    }
};
