<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class KDSController extends Controller
{
    /**
     * Tampilkan halaman Kitchen Display System (KDS) Dapur.
     */
    public function showKDS($alias)
    {
        $tenant = app('tenant');

        $orders = Order::where('tenant_id', $tenant->id)
            ->whereIn('kitchen_status', ['cooking', 'ready'])
            ->with('items.product')
            ->orderBy('created_at', 'asc')
            ->get();

        return view('owner.kds', compact('tenant', 'alias', 'orders'));
    }

    /**
     * Update status pesanan dari 'cooking' menjadi 'ready' (Selesai Memasak).
     */
    public function completeOrder(Request $request, $alias, $orderId)
    {
        $tenant = app('tenant');

        $order = Order::where('tenant_id', $tenant->id)
            ->where('id', $orderId)
            ->first();

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Pesanan tidak ditemukan.'], 404);
        }

        // Update status ke ready
        $order->status = 'ready';
        $order->save();

        // Kirim notifikasi event real-time via locomo.gateway
        \App\Services\GatewayService::emit(
            'caffe-' . $tenant->id . '-kitchen',
            'order-ready',
            [
                'id' => $order->id,
                'status' => 'ready',
                'order_number' => $order->order_number,
                'table_number' => $order->table_number,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Pesanan selesai dimasak dan siap diantar!',
            'order' => $order
        ]);
    }
}
