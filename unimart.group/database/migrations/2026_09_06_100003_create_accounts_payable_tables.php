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
        // Buku Utang Toko ke Supplier
        Schema::create('accounts_payable', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->foreignId('purchase_id')->constrained('purchases')->cascadeOnDelete();
            $table->string('payable_number'); // AP-20260906-0001
            $table->bigInteger('total_debt_amount');
            $table->bigInteger('remaining_amount');
            $table->date('due_date')->nullable();
            $table->string('status')->default('unpaid'); // unpaid, partially_paid, paid
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'payable_number']);
            $table->index(['tenant_id', 'status', 'due_date']);
        });

        // Riwayat Pembayaran / Cicilan Utang ke Supplier
        Schema::create('payable_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('payable_id')->constrained('accounts_payable')->cascadeOnDelete();
            $table->foreignId('shift_id')->nullable()->constrained('shifts')->nullOnDelete();
            $table->string('payment_method')->default('cash'); // cash, bank_transfer
            $table->bigInteger('amount_paid');
            $table->foreignId('processed_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->text('receipt_image_url')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'payable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payable_payments');
        Schema::dropIfExists('accounts_payable');
    }
};
