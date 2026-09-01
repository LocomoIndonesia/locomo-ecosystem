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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignId('shift_id')->nullable()->constrained('shifts')->onDelete('set null'); // Bisa nullable jika self-order pelanggan belum dimasukkan ke shift kasir aktif
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('set null');
            $table->foreignId('table_id')->nullable()->constrained('tables')->onDelete('set null');
            $table->string('order_number')->unique();
            $table->string('customer_name')->nullable(); // Nama pelanggan opsional dari kasir
            $table->string('order_type')->default('dine_in'); // dine_in, takeaway
            $table->string('kitchen_status')->default('pending'); // pending, processing, ready, delivered
            $table->string('payment_status')->default('unpaid'); // unpaid, partial, paid
            $table->integer('total_amount');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
