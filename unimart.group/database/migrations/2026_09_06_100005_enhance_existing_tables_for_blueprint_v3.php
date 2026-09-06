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
        // 1. Tambah kolom pada tenants
        Schema::table('tenants', function (Blueprint $table) {
            if (!Schema::hasColumn('tenants', 'business_type')) {
                $table->string('business_type')->default('caffe');
            }
            if (!Schema::hasColumn('tenants', 'owner_name')) {
                $table->string('owner_name')->nullable();
            }
            if (!Schema::hasColumn('tenants', 'owner_email')) {
                $table->string('owner_email')->nullable();
            }
            if (!Schema::hasColumn('tenants', 'owner_phone')) {
                $table->string('owner_phone')->nullable();
            }
            if (!Schema::hasColumn('tenants', 'status')) {
                $table->string('status')->default('trial'); // trial, active, expired, suspended
            }
            if (!Schema::hasColumn('tenants', 'trial_ends_at')) {
                $table->timestamp('trial_ends_at')->nullable();
            }
            if (!Schema::hasColumn('tenants', 'subscription_ends_at')) {
                $table->timestamp('subscription_ends_at')->nullable();
            }
            if (!Schema::hasColumn('tenants', 'is_marketplace_enabled')) {
                $table->boolean('is_marketplace_enabled')->default(true);
            }
            if (!Schema::hasColumn('tenants', 'city')) {
                $table->string('city')->nullable();
            }
            if (!Schema::hasColumn('tenants', 'address')) {
                $table->text('address')->nullable();
            }
        });

        // 2. Tambah kolom pada products
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'cost_price')) {
                $table->bigInteger('cost_price')->default(0); // HPP barang
            }
            if (!Schema::hasColumn('products', 'min_stock')) {
                $table->integer('min_stock')->default(5); // Alert stok menipis
            }
            if (!Schema::hasColumn('products', 'is_published')) {
                $table->boolean('is_published')->default(true); // Tampil di marketplace
            }
            if (!Schema::hasColumn('products', 'is_available')) {
                $table->boolean('is_available')->default(true);
            }
            if (!Schema::hasColumn('products', 'metadata')) {
                $table->jsonb('metadata')->nullable();
            }
        });

        // 3. Tambah kolom pada shifts
        Schema::table('shifts', function (Blueprint $table) {
            if (!Schema::hasColumn('shifts', 'total_cash_sales')) {
                $table->bigInteger('total_cash_sales')->default(0);
            }
            if (!Schema::hasColumn('shifts', 'total_debt_sales')) {
                $table->bigInteger('total_debt_sales')->default(0);
            }
            if (!Schema::hasColumn('shifts', 'total_supplier_cash_out')) {
                $table->bigInteger('total_supplier_cash_out')->default(0);
            }
            if (!Schema::hasColumn('shifts', 'cash_difference')) {
                $table->bigInteger('cash_difference')->nullable();
            }
        });

        // 4. Tambah kolom pada customers
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'current_debt')) {
                $table->bigInteger('current_debt')->default(0); // Saldo piutang berjalan
            }
            if (!Schema::hasColumn('customers', 'credit_limit')) {
                $table->bigInteger('credit_limit')->default(0);
            }
            if (!Schema::hasColumn('customers', 'address')) {
                $table->text('address')->nullable();
            }
        });

        // 5. Tambah kolom pada payments
        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'gateway_name')) {
                $table->string('gateway_name')->nullable(); // midtrans, xendit
            }
            if (!Schema::hasColumn('payments', 'gateway_transaction_id')) {
                $table->string('gateway_transaction_id')->nullable();
            }
            if (!Schema::hasColumn('payments', 'gateway_response')) {
                $table->jsonb('gateway_response')->nullable();
            }
        });

        // 6. Tambah kolom pada expenses
        Schema::table('expenses', function (Blueprint $table) {
            if (!Schema::hasColumn('expenses', 'category')) {
                $table->string('category')->default('operasional');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rollback columns if needed
    }
};
