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
        Schema::table('ecosystem_services', function (Blueprint $table) {
            if (!Schema::hasColumn('ecosystem_services', 'icon')) {
                $table->string('icon')->default('storefront')->after('name');
            }
            if (!Schema::hasColumn('ecosystem_services', 'category')) {
                $table->string('category')->default('BISNIS')->after('code');
            }
            if (!Schema::hasColumn('ecosystem_services', 'description')) {
                $table->text('description')->nullable()->after('category');
            }
            if (!Schema::hasColumn('ecosystem_services', 'wizard_schema')) {
                $table->json('wizard_schema')->nullable()->after('description');
            }
            if (!Schema::hasColumn('ecosystem_services', 'price_gold')) {
                $table->bigInteger('price_gold')->default(0)->after('wizard_schema');
            }
            if (!Schema::hasColumn('ecosystem_services', 'price_fiat')) {
                $table->decimal('price_fiat', 12, 2)->default(0)->after('price_gold');
            }
            if (!Schema::hasColumn('ecosystem_services', 'service_url')) {
                $table->string('service_url')->nullable()->after('price_fiat');
            }
            if (!Schema::hasColumn('ecosystem_services', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('status');
            }
        });

        if (!Schema::hasTable('service_licenses')) {
            Schema::create('service_licenses', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('service_id');
                $table->uuid('user_id'); // License holder / beneficiary
                $table->uuid('registered_by_id'); // Agent who registered
                $table->string('target_type')->default('SELF'); // SELF, OTHER
                $table->json('beneficiary_data')->nullable(); // Wizard form values
                $table->string('license_code')->unique(); // E.g., LIC-UNI-XXXX
                $table->string('status')->default('ACTIVE'); // ACTIVE, PENDING_PAYMENT, EXPIRED, SUSPENDED
                $table->string('payment_method')->default('GOLD'); // GOLD, FIAT, FREE
                $table->decimal('amount_paid', 12, 2)->default(0);
                $table->string('currency')->default('GOLD'); // GOLD, IDR
                $table->timestamp('expires_at')->nullable();
                $table->string('external_reference_id')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->foreign('service_id')->references('id')->on('ecosystem_services')->onDelete('cascade');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('registered_by_id')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_licenses');

        Schema::table('ecosystem_services', function (Blueprint $table) {
            $table->dropColumn([
                'icon',
                'category',
                'description',
                'wizard_schema',
                'price_gold',
                'price_fiat',
                'service_url',
                'is_active',
            ]);
        });
    }
};
