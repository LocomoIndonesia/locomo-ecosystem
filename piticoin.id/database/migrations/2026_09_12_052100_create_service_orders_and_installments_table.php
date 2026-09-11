<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('service_id')->constrained('ecosystem_services')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('registered_by_id')->constrained('users')->cascadeOnDelete();
            $table->string('license_code', 64)->unique();
            $table->string('package_tier', 100)->nullable();
            $table->string('payment_type', 20)->default('FULL'); // 'FULL' or 'INSTALLMENT'
            $table->decimal('total_amount_gold', 16, 2)->default(0);
            $table->decimal('total_amount_fiat', 16, 2)->default(0);
            $table->decimal('paid_amount_gold', 16, 2)->default(0);
            $table->decimal('paid_amount_fiat', 16, 2)->default(0);
            $table->string('status', 30)->default('ACTIVE'); // 'PENDING', 'ACTIVE', 'COMPLETED', 'CANCELLED'
            $table->jsonb('order_data')->nullable();
            $table->timestamps();

            $table->index(['service_id', 'status']);
            $table->index(['user_id', 'status']);
            $table->index(['registered_by_id']);
        });

        Schema::create('order_installments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('service_order_id')->constrained('service_orders')->cascadeOnDelete();
            $table->integer('installment_number')->default(1);
            $table->decimal('amount_gold', 16, 2)->default(0);
            $table->decimal('amount_fiat', 16, 2)->default(0);
            $table->string('currency', 10)->default('GOLD'); // 'GOLD' or 'IDR'
            $table->string('payment_method', 20)->default('GOLD'); // 'GOLD' or 'FIAT'
            $table->string('status', 20)->default('PAID'); // 'PENDING', 'PAID', 'FAILED'
            $table->boolean('commissions_distributed')->default(false);
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['service_order_id', 'installment_number']);
            $table->index(['status', 'commissions_distributed']);
        });

        Schema::create('affiliate_commissions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('service_id')->constrained('ecosystem_services')->cascadeOnDelete();
            $table->foreignUuid('service_order_id')->constrained('service_orders')->cascadeOnDelete();
            $table->foreignUuid('order_installment_id')->nullable()->constrained('order_installments')->nullOnDelete();
            $table->foreignUuid('beneficiary_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('upline_user_id')->constrained('users')->cascadeOnDelete();
            $table->integer('level')->default(1);
            $table->decimal('percentage', 6, 2)->default(0);
            $table->decimal('amount_gold', 16, 2)->default(0);
            $table->decimal('amount_fiat', 16, 2)->default(0);
            $table->string('currency', 10)->default('GOLD');
            $table->string('status', 20)->default('SETTLED'); // 'SETTLED', 'PENDING'
            $table->foreignUuid('ledger_id')->nullable()->constrained('ledgers')->nullOnDelete();
            $table->timestamps();

            $table->index(['service_id', 'level']);
            $table->index(['upline_user_id', 'status']);
            $table->index(['service_order_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('affiliate_commissions');
        Schema::dropIfExists('order_installments');
        Schema::dropIfExists('service_orders');
    }
};
