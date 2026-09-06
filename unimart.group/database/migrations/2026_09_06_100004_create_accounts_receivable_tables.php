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
        // Buku Piutang Pelanggan (Bon Warung / Customer Debt)
        Schema::create('accounts_receivable', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('receivable_number'); // AR-20260906-0001
            $table->bigInteger('total_debt_amount');
            $table->bigInteger('remaining_amount');
            $table->date('due_date')->nullable();
            $table->string('status')->default('unpaid'); // unpaid, partially_paid, paid, bad_debt
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'receivable_number']);
            $table->index(['tenant_id', 'status', 'due_date']);
        });

        // Riwayat Setoran / Cicilan Pelanggan
        Schema::create('receivable_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('receivable_id')->constrained('accounts_receivable')->cascadeOnDelete();
            $table->foreignId('shift_id')->nullable()->constrained('shifts')->nullOnDelete();
            $table->string('payment_method')->default('cash'); // cash, qris_online, bank_transfer
            $table->bigInteger('amount_paid');
            $table->foreignId('processed_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'receivable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('receivable_payments');
        Schema::dropIfExists('accounts_receivable');
    }
};
