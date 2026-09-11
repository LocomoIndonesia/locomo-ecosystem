<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ecosystem_services', function (Blueprint $table) {
            $table->uuid('owner_id')->nullable()->after('id');
            $table->string('service_type', 20)->default('EXTERNAL')->after('category'); // 'EXTERNAL' or 'INTERNAL'
            $table->boolean('allows_installments')->default(false)->after('is_active');
            $table->decimal('min_down_payment_gold', 16, 2)->nullable()->after('allows_installments');
            $table->decimal('min_down_payment_fiat', 16, 2)->nullable()->after('min_down_payment_gold');
            $table->jsonb('commission_rules')->nullable()->after('min_down_payment_fiat');

            $table->foreign('owner_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('ecosystem_services', function (Blueprint $table) {
            $table->dropForeign(['owner_id']);
            $table->dropColumn([
                'owner_id',
                'service_type',
                'allows_installments',
                'min_down_payment_gold',
                'min_down_payment_fiat',
                'commission_rules',
            ]);
        });
    }
};
