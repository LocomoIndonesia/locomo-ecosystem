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
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained('suppliers')->restrictOnDelete();
            $table->string('purchase_number'); // PO-20260906-0001
            $table->string('supplier_invoice_no')->nullable(); // No nota/faktur dari supplier
            $table->date('purchase_date')->default(DB::raw('CURRENT_DATE'));
            $table->bigInteger('total_amount')->default(0);
            $table->string('payment_status')->default('paid'); // paid, debt, partial
            $table->string('payment_method')->default('cash'); // cash, bank_transfer, debt
            $table->date('due_date')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['tenant_id', 'purchase_number']);
            $table->index(['tenant_id', 'purchase_date']);
        });

        Schema::create('purchase_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('purchase_id')->constrained('purchases')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->integer('quantity');
            $table->bigInteger('buy_price'); // Harga modal per unit
            $table->bigInteger('subtotal');
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'purchase_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_items');
        Schema::dropIfExists('purchases');
    }
};
