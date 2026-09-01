<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Table;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Exception;

class OrderManagerService
{
    /**
     * Membuat order beserta items dan mengurus pemotongan stock.
     */
    public function createOrder(array $orderData, array $items): Order
    {
        return DB::transaction(function () use ($orderData, $items) {
            // Generate order number if not exists
            if (!isset($orderData['order_number'])) {
                $orderData['order_number'] = 'ORD-' . strtoupper(Str::random(8));
            }
            
            // Set initial status
            $orderData['kitchen_status'] = 'pending';
            $orderData['payment_status'] = 'unpaid';
            $orderData['total_amount'] = 0; // will calculate below

            $order = Order::create($orderData);

            $totalAmount = 0;

            foreach ($items as $item) {
                $product = Product::findOrFail($item['product_id']);
                
                if ($product->stock < $item['quantity']) {
                    throw new Exception("Stock not sufficient for product: {$product->name}");
                }
                
                // Reserve stock
                $product->decrement('stock', $item['quantity']);

                $order->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $product->price, // snapshot harga
                    'status' => 'pending',
                    'notes' => $item['notes'] ?? null,
                ]);

                $totalAmount += ($product->price * $item['quantity']);
            }

            // Update total amount
            $order->update(['total_amount' => $totalAmount]);

            // Update Table Status if table_id is provided
            if (isset($orderData['table_id'])) {
                $table = Table::find($orderData['table_id']);
                if ($table && $table->status === 'available') {
                    $table->update(['status' => 'occupied']);
                }
            }

            return $order;
        });
    }

    /**
     * Menambahkan pembayaran (Split bill / Full)
     */
    public function addPayment(Order $order, array $paymentData): Payment
    {
        return DB::transaction(function () use ($order, $paymentData) {
            $user = auth()->user();
            $processedByUserId = null;
            $processedByName = null;
            if ($user) {
                $processedByUserId = $user->id;
                $roleLabel = str_replace('_', ' ', $user->role);
                if ($user->isOwner() && session('owner_acting_role')) {
                    $roleLabel = session('owner_acting_role');
                }
                $processedByName = $user->name . ' (' . ucwords($roleLabel) . ')';
            }

            $payment = $order->payments()->create([
                'payment_method' => $paymentData['payment_method'],
                'amount_paid' => $paymentData['amount_paid'],
                'status' => 'success',
                'processed_by_user_id' => $processedByUserId,
                'processed_by_name' => $processedByName,
            ]);

            // Calculate total paid so far
            $totalPaid = $order->payments()->where('status', 'success')->sum('amount_paid');

            if ($totalPaid >= $order->total_amount) {
                $order->update(['payment_status' => 'paid']);
            } else {
                $order->update(['payment_status' => 'partial']);
            }

            return $payment;
        });
    }
}
