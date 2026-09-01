<?php

namespace App\Observers;

use App\Models\OrderItem;
use App\Models\WastedItem;

class OrderItemObserver
{
    public function updated(OrderItem $orderItem): void
    {
        if ($orderItem->isDirty('status') && $orderItem->status === 'void') {
            $originalStatus = $orderItem->getOriginal('status');
            
            // If it was already cooked or ready, move to wasted items
            if (in_array($originalStatus, ['cooking', 'ready'])) {
                WastedItem::create([
                    'tenant_id' => $orderItem->order->tenant_id,
                    'order_id' => $orderItem->order_id,
                    'product_id' => $orderItem->product_id,
                    'quantity' => $orderItem->quantity,
                    'price' => $orderItem->price,
                    'reason' => 'Voided after ' . $originalStatus,
                    'original_status' => $originalStatus,
                ]);
            } else if ($originalStatus === 'pending') {
                // Return stock if it was still pending
                $orderItem->product()->increment('stock', $orderItem->quantity);
            }
        }
    }
}
