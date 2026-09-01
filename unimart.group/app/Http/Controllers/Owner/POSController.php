<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class POSController extends Controller
{
    /**
     * Tampilkan halaman POS kasir.
     */
    public function showPOS($alias)
    {
        $tenant = app('tenant');
        $products = Product::where('tenant_id', $tenant->id)->get();

        // Cari shift aktif kasir saat ini
        $shift = Shift::where('tenant_id', $tenant->id)
            ->where('user_id', Auth::id())
            ->whereNull('closed_at')
            ->first();

        // Jika tidak ada shift aktif, kita buatkan shift otomatis untuk mempermudah simulasi/demo
        if (!$shift) {
            $shift = Shift::create([
                'tenant_id' => $tenant->id,
                'user_id' => Auth::id(),
                'opened_at' => now(),
                'starting_cash' => 500000,
                'system_ending_cash' => 500000,
            ]);
        }

        $tables = \App\Models\Table::where('tenant_id', $tenant->id)->get();

        return view('owner.pos', compact('tenant', 'alias', 'products', 'shift', 'tables'));
    }

    /**
     * Proses transaksi checkout dari kasir.
     */
    public function checkout(Request $request, $alias)
    {
        $tenant = app('tenant');
        
        $request->validate([
            'items' => ['required', 'array'],
            'items.*.id' => ['required', 'exists:products,id'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'order_type' => ['required', 'string', 'in:dine_in,takeaway'],
            'table_id' => ['nullable', 'exists:tables,id'],
            'payment_method' => ['required', 'string'],
            'customer_name' => ['nullable', 'string', 'max:100'],
            'is_online' => ['nullable', 'boolean'],
            'delivery_address' => ['nullable', 'string'],
            'delivery_latitude' => ['nullable', 'numeric'],
            'delivery_longitude' => ['nullable', 'numeric'],
            'delivery_fee' => ['nullable', 'numeric'],
        ]);

        $shift = Shift::where('tenant_id', $tenant->id)
            ->where('user_id', Auth::id())
            ->whereNull('closed_at')
            ->first();

        if (!$shift) {
            return response()->json(['success' => false, 'message' => 'Tidak ada shift kasir yang aktif.'], 400);
        }

        try {
            $order = DB::transaction(function () use ($request, $tenant, $shift) {
                $totalAmount = 0;
                $orderItemsData = [];

                // 1. Loop dan hitung total serta lock stock
                foreach ($request->items as $item) {
                    $product = Product::where('id', $item['id'])
                        ->lockForUpdate()
                        ->first();

                    if ($product->stock < $item['qty']) {
                        throw new \Exception("Stok tidak mencukupi untuk menu: " . $product->name);
                    }

                    // Potong stok
                    $product->decrement('stock', $item['qty']);

                    $subtotal = $product->price * $item['qty'];
                    $totalAmount += $subtotal;

                    $orderItemsData[] = [
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'quantity' => $item['qty'],
                        'price' => $product->price,
                    ];
                }

                // Hitung ongkir jika online delivery
                $deliveryFee = 0;
                $isOnline = (bool) $request->is_online;
                if ($isOnline) {
                    $deliveryFee = floatval($request->delivery_fee ?? 0);
                    $totalAmount += $deliveryFee;
                }

                // Dapatkan data user pembuat order
                $user = Auth::user();
                $processedByUserId = $user ? $user->id : null;
                $processedByName = null;
                if ($user) {
                    $roleLabel = str_replace('_', ' ', $user->role);
                    if ($user->isOwner() && session('owner_acting_role')) {
                        $roleLabel = session('owner_acting_role');
                    }
                    $processedByName = $user->name . ' (' . ucwords($roleLabel) . ')';
                }

                // 2. Buat Order baru
                $isPaylater = $request->payment_method === 'later';
                $order = Order::create([
                    'tenant_id' => $tenant->id,
                    'shift_id' => $shift->id,
                    'order_number' => 'ORD-' . date('YmdHis') . '-' . rand(10, 99),
                    'customer_name' => $request->customer_name ?? null,
                    'order_type' => $isOnline ? 'takeaway' : $request->order_type,
                    'table_id' => (!$isOnline && $request->order_type === 'dine_in') ? $request->table_id : null,
                    'kitchen_status' => 'pending',
                    // Jika bayar nanti: unpaid. Jika metode pembayaran lain: langsung paid
                    'payment_status' => $isPaylater ? 'unpaid' : 'paid',
                    'total_amount' => $totalAmount,
                    'order_source' => $isOnline ? 'online' : 'pos',
                    'delivery_address' => $isOnline ? $request->delivery_address : null,
                    'delivery_latitude' => $isOnline ? $request->delivery_latitude : null,
                    'delivery_longitude' => $isOnline ? $request->delivery_longitude : null,
                    'delivery_fee' => $isOnline ? $deliveryFee : 0,
                    'processed_by_user_id' => $processedByUserId,
                    'processed_by_name' => $processedByName,
                ]);

                // 3. Simpan item pesanan
                foreach ($orderItemsData as $itemData) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $itemData['product_id'],
                        'quantity' => $itemData['quantity'],
                        'price' => $itemData['price'],
                        'status' => 'pending',
                    ]);
                }

                // 4. Catat Payment (hanya jika bukan "bayar nanti")
                if (!$isPaylater) {
                    $order->payments()->create([
                        'payment_method' => $request->payment_method,
                        'amount_paid' => $totalAmount,
                        'status' => 'success',
                        'processed_by_user_id' => $processedByUserId,
                        'processed_by_name' => $processedByName,
                    ]);
                }

                // Update status meja jika dine_in dan bukan online
                if (!$isOnline && $request->order_type === 'dine_in' && $request->table_id) {
                    $table = \App\Models\Table::find($request->table_id);
                    if ($table && $table->status === 'available') {
                        $table->update(['status' => 'occupied']);
                    }
                }

                // Update system ending cash jika pembayaran tunai (cash) dan bukan bayar nanti
                if ($request->payment_method === 'cash' && !$isPaylater) {
                    $shift->increment('system_ending_cash', $totalAmount);
                }

                // Return order dengan relasi items untuk broadcast data lengkap
                $order->load('items.product');
                return $order;
            });

            // 4. Kirim trigger HTTP POST internal ke Node.js gateway (locomo.gateway)
            $gatewayUrl = 'http://gateway:3000/emit';
            $channelName = 'caffe-' . $tenant->id . '-kitchen';
            
            // Siapkan payload data pesanan untuk dapur
            $payload = [
                'channel' => $channelName,
                'event' => 'new-order',
                'data' => [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'table_number' => $order->table_number,
                    'status' => $order->status,
                    'total_amount' => $order->total_amount,
                    'created_at' => $order->created_at->toIso8601String(),
                    'items' => $order->items->map(function ($item) {
                        return [
                            'name' => $item->product->name,
                            'qty' => $item->quantity,
                        ];
                    })->toArray()
                ]
            ];

            // Kirim request ke gateway Node.js (abaikan timeout untuk kelancaran lokal)
            Http::timeout(3)->post($gatewayUrl, $payload);

            return response()->json([
                'success' => true,
                'message' => 'Pesanan berhasil diproses!',
                'order' => $order
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Proses transaksi checkout dari tamu/pelanggan via QR Code.
     */
    public function customerCheckout(Request $request, $alias)
    {
        $tenant = app('tenant');
        
        $request->validate([
            'items' => ['required', 'array'],
            'items.*.id' => ['required', 'exists:products,id'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'table_number' => ['nullable', 'string'],
            'payment_method' => ['required', 'string'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'is_online' => ['nullable', 'boolean'],
            'delivery_address' => ['nullable', 'string'],
            'delivery_latitude' => ['nullable', 'numeric'],
            'delivery_longitude' => ['nullable', 'numeric'],
            'delivery_fee' => ['nullable', 'numeric'],
        ]);

        try {
            $order = DB::transaction(function () use ($request, $tenant) {
                $totalAmount = 0;
                $orderItemsData = [];

                foreach ($request->items as $item) {
                    $product = Product::where('id', $item['id'])
                        ->lockForUpdate()
                        ->first();

                    if ($product->stock < $item['qty']) {
                        throw new \Exception("Stok tidak mencukupi untuk menu: " . $product->name);
                    }

                    // Potong stok
                    $product->decrement('stock', $item['qty']);

                    $subtotal = $product->price * $item['qty'];
                    $totalAmount += $subtotal;

                    $orderItemsData[] = [
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'quantity' => $item['qty'],
                        'price' => $product->price,
                    ];
                }

                // Hitung ongkir jika online delivery
                $deliveryFee = 0;
                $isOnline = (bool) $request->is_online;
                if ($isOnline) {
                    $deliveryFee = floatval($request->delivery_fee ?? 0);
                    $totalAmount += $deliveryFee;
                }

                // Resolusi table_id dari table_number string jika bukan online
                $table = null;
                if (!$isOnline && $request->table_number) {
                    $tableName = $request->table_number;
                    if (is_numeric($tableName)) {
                        $tableName = 'Meja ' . $tableName;
                    }
                    $table = \App\Models\Table::where('tenant_id', $tenant->id)
                        ->where('name', $tableName)
                        ->first();
                }

                // Buat Order baru
                $order = Order::create([
                    'tenant_id' => $tenant->id,
                    'shift_id' => null, // Tidak terikat shift kasir saat order dibuat pelanggan
                    'order_number' => 'SEL-' . date('YmdHis') . '-' . rand(10, 99),
                    'order_type' => $isOnline ? 'takeaway' : 'dine_in',
                    'customer_name' => $request->customer_name ?? null,
                    'table_id' => $table ? $table->id : null,
                    'kitchen_status' => 'pending',
                    'payment_status' => 'unpaid', // Masih belum bayar
                    'total_amount' => $totalAmount,
                    'order_source' => $isOnline ? 'online' : 'qr',
                    'delivery_address' => $isOnline ? $request->delivery_address : null,
                    'delivery_latitude' => $isOnline ? $request->delivery_latitude : null,
                    'delivery_longitude' => $isOnline ? $request->delivery_longitude : null,
                    'delivery_fee' => $isOnline ? $deliveryFee : 0,
                    'processed_by_user_id' => null,
                    'processed_by_name' => $isOnline ? 'Pelanggan (Online)' : 'Pelanggan via Unimart QR',
                ]);

                foreach ($orderItemsData as $itemData) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $itemData['product_id'],
                        'quantity' => $itemData['quantity'],
                        'price' => $itemData['price'],
                        'status' => 'pending',
                    ]);
                }

                // Update status meja jika terdeteksi dan bukan online
                if ($table && $table->status === 'available') {
                    $table->update(['status' => 'occupied']);
                }

                $order->load('items.product');
                return $order;
            });

            // Kirim trigger HTTP POST internal ke Node.js gateway
            $gatewayUrl = 'http://gateway:3000/emit';
            $channelName = 'caffe-' . $tenant->id . '-kitchen';
            
            $payload = [
                'channel' => $channelName,
                'event' => 'new-order',
                'data' => [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'table_number' => $order->table ? $order->table->name : ($order->order_source === 'online' ? 'Online' : 'Counter'),
                    'status' => $order->kitchen_status,
                    'total_amount' => $order->total_amount,
                    'order_source' => $order->order_source,
                    'delivery_address' => $order->delivery_address,
                    'delivery_fee' => $order->delivery_fee,
                    'customer_name' => $order->customer_name,
                    'created_at' => $order->created_at->toIso8601String(),
                    'items' => $order->items->map(function ($item) {
                        return [
                            'name' => $item->product->name,
                            'qty' => $item->quantity,
                        ];
                    })->toArray()
                ]
            ];

            Http::timeout(3)->post($gatewayUrl, $payload);

            return response()->json([
                'success' => true,
                'message' => 'Pesanan berhasil dikirim ke dapur!',
                'order' => $order
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
