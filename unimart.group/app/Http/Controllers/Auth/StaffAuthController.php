<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Shift;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Expense;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class StaffAuthController extends Controller
{
    /**
     * Tampilkan form login staff.
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirectUser(Auth::user());
        }
        return view('auth.staff-login');
    }

    /**
     * Proses login staff (Email / No. HP + Password).
     */
    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        $tenantId = app()->bound('tenant_id') ? app('tenant_id') : session('tenant_id');
        $tenant = app()->bound('tenant') ? app('tenant') : ($tenantId ? Tenant::find($tenantId) : null);

        // Cari user berdasarkan email atau nomor telepon
        $userQuery = User::query();
        if ($tenantId) {
            $userQuery->where('tenant_id', $tenantId);
        }

        $user = $userQuery->where(function ($query) use ($request) {
                $query->where('email', $request->login)
                      ->orWhere('phone', $request->login);
            })
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()->withErrors(['login' => 'Kredensial login salah atau tidak terdaftar.']);
        }

        // Login staff
        Auth::login($user);

        return $this->redirectUser($user);
    }

    /**
     * Logout staff.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Tampilkan form ganti password.
     */
    public function showChangePassword()
    {
        return view('auth.change-password');
    }

    /**
     * Proses ganti password.
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Password saat ini tidak cocok dengan data kami.']);
        }

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return back()->with('success', 'Password berhasil diubah!');
    }

    /**
     * Tampilkan dashboard pekerja/staf.
     */
    public function showDashboard(Request $request)
    {
        $tenant = app('tenant');
        $user = Auth::user();

        // Fallback jika tenant tidak teridentifikasi dari middleware, ambil dari user
        if (!$tenant && $user->tenant_id) {
            $tenant = \App\Models\Tenant::find($user->tenant_id);
            if ($tenant) {
                app()->instance('tenant_id', $tenant->id);
                app()->instance('tenant', $tenant);
            }
        }

        $alias = $tenant ? $tenant->domain : 'default';

        // Tentukan peran aktif (effective role)
        $actingRole = $user->role;
        if ($user->isOwner()) {
            $actingRole = session('owner_acting_role', 'owner');
        }

        $data = [
            'tenant' => $tenant,
            'user' => $user,
            'alias' => $alias,
            'actingRole' => $actingRole,
        ];

        if (!$tenant) {
            return view('staff.dashboard', $data);
        }

        $loadCashier = ($actingRole === 'cashier' || $actingRole === 'owner');
        $loadChef = ($actingRole === 'chef' || $actingRole === 'owner');
        $loadWaiter = ($actingRole === 'waiter' || $actingRole === 'owner');
        $loadGudang = ($actingRole === 'admin_gudang' || $actingRole === 'owner');
        $loadKurir = ($actingRole === 'kurir' || $actingRole === 'owner');

        // 1. Load Data Cashier / POS
        if ($loadCashier) {
            // Dapatkan shift aktif
            $shift = Shift::withoutGlobalScope(\App\Tenant\TenantScope::class)
                ->where('tenant_id', $tenant->id)
                ->where('user_id', $user->id)
                ->whereNull('closed_at')
                ->first();

            $data['shift'] = $shift;

            if ($shift) {
                // Total penjualan di shift ini
                $data['sales_total'] = Order::withoutGlobalScope(\App\Tenant\TenantScope::class)
                    ->where('tenant_id', $tenant->id)
                    ->where('shift_id', $shift->id)
                    ->sum('total_amount');
                $data['sales_count'] = Order::withoutGlobalScope(\App\Tenant\TenantScope::class)
                    ->where('tenant_id', $tenant->id)
                    ->where('shift_id', $shift->id)
                    ->count();
                $data['expenses_total'] = Expense::withoutGlobalScope(\App\Tenant\TenantScope::class)
                    ->where('tenant_id', $tenant->id)
                    ->where('shift_id', $shift->id)
                    ->sum('amount');
            }

            // Daftar semua order hari ini dari toko ini
            $data['orders'] = Order::withoutGlobalScope(\App\Tenant\TenantScope::class)
                ->where('tenant_id', $tenant->id)
                ->with(['items.product', 'payments', 'table'])
                ->orderBy('created_at', 'desc')
                ->take(30)
                ->get();

            // Produk aktif untuk membuat order baru dari dashboard
            $data['products'] = \App\Models\Product::withoutGlobalScope(\App\Tenant\TenantScope::class)
                ->where('tenant_id', $tenant->id)
                ->where('stock', '>', 0)
                ->get();

            // Meja yang tersedia atau sedang dipakai
            $data['tables'] = \App\Models\Table::withoutGlobalScope(\App\Tenant\TenantScope::class)
                ->where('tenant_id', $tenant->id)
                ->get();
        }

        // 2. Load Data Chef / KDS
        if ($loadChef) {
            // Jumlah masakan yang diselesaikan hari ini
            $data['prepared_count'] = OrderItem::withoutGlobalScope(\App\Tenant\TenantScope::class)
                ->where('status', 'ready')
                ->whereHas('order', function ($query) use ($tenant) {
                    $query->withoutGlobalScope(\App\Tenant\TenantScope::class)
                          ->where('tenant_id', $tenant->id);
                })
                ->whereDate('updated_at', today())
                ->count();

            // Jumlah masakan yang sedang diproses
            $data['pending_count'] = OrderItem::withoutGlobalScope(\App\Tenant\TenantScope::class)
                ->whereIn('status', ['pending', 'cooking'])
                ->whereHas('order', function ($query) use ($tenant) {
                    $query->withoutGlobalScope(\App\Tenant\TenantScope::class)
                          ->where('tenant_id', $tenant->id);
                })
                ->count();
        }

        // 3. Load Data Waiter
        if ($loadWaiter) {
            // Jumlah orderan yang diantar hari ini
            $data['delivered_count'] = Order::withoutGlobalScope(\App\Tenant\TenantScope::class)
                ->where('tenant_id', $tenant->id)
                ->where('kitchen_status', 'delivered')
                ->whereDate('updated_at', today())
                ->count();
        }

        // 4. Load Data Admin Gudang
        if ($loadGudang) {
            $warehouseProducts = \App\Models\Product::withoutGlobalScope(\App\Tenant\TenantScope::class)
                ->where('tenant_id', $tenant->id)
                ->orderBy('name', 'asc')
                ->get();

            $data['warehouse_products'] = $warehouseProducts;
            $data['total_products'] = $warehouseProducts->count();
            $data['low_stock_count'] = $warehouseProducts->filter(fn($p) => $p->stock > 0 && $p->stock <= 5)->count();
            $data['out_of_stock_count'] = $warehouseProducts->filter(fn($p) => $p->stock === 0)->count();
        }

        // 5. Load Data Kurir
        if ($loadKurir) {
            // Jika user adalah owner, kita sudah load orders lengkap di atas. Jika kurir murni, load tersaring.
            if ($actingRole === 'kurir') {
                $orders = Order::withoutGlobalScope(\App\Tenant\TenantScope::class)
                    ->where('tenant_id', $tenant->id)
                    ->whereIn('kitchen_status', ['ready', 'delivered'])
                    ->with(['items.product', 'payments', 'table'])
                    ->orderBy('updated_at', 'desc')
                    ->get();

                $data['orders'] = $orders;
                $data['pending_deliveries'] = $orders->filter(fn($o) => $o->kitchen_status === 'ready')->count();
                $data['delivered_today'] = $orders->filter(fn($o) => $o->kitchen_status === 'delivered' && $o->updated_at->isToday())->count();
            } else {
                // Untuk owner, hitung dari list orders yang lengkap
                $allOrders = $data['orders'] ?? collect();
                $data['pending_deliveries'] = $allOrders->filter(fn($o) => $o->kitchen_status === 'ready' && ($o->order_source === 'online' || $o->delivery_address))->count();
                $data['delivered_today'] = $allOrders->filter(fn($o) => $o->kitchen_status === 'delivered' && $o->updated_at->isToday())->count();
            }
        }

        return view('staff.dashboard', $data);
    }

    /**
     * Membuka shift baru (Kasir).
     */
    public function startShift(Request $request)
    {
        $tenant = app('tenant');
        $request->validate([
            'starting_cash' => 'required|integer|min:0',
        ]);

        Shift::create([
            'tenant_id' => $tenant->id,
            'user_id' => Auth::id(),
            'opened_at' => now(),
            'starting_cash' => $request->starting_cash,
            'system_ending_cash' => $request->starting_cash,
        ]);

        return back()->with('success', 'Shift baru berhasil dimulai!');
    }

    /**
     * Menutup shift aktif (Kasir).
     */
    public function closeShift(Request $request)
    {
        $tenant = app('tenant');
        $shift = Shift::where('tenant_id', $tenant->id)
            ->where('user_id', Auth::id())
            ->whereNull('closed_at')
            ->first();

        if ($shift) {
            $request->validate([
                'actual_ending_cash' => 'required|integer|min:0',
                'notes' => 'nullable|string',
            ]);

            $shift->update([
                'closed_at' => now(),
                'actual_ending_cash' => $request->actual_ending_cash,
                'notes' => $request->notes,
            ]);
        }

        return back()->with('success', 'Shift berhasil ditutup dan kas diserahkan!');
    }

    /**
     * Mencatat pengeluaran petty cash (Kasir).
     */
    public function addExpense(Request $request)
    {
        $tenant = app('tenant');
        $shift = Shift::where('tenant_id', $tenant->id)
            ->where('user_id', Auth::id())
            ->whereNull('closed_at')
            ->first();

        if (!$shift) {
            return back()->withErrors(['expense' => 'Tidak ada shift aktif untuk mencatat pengeluaran.']);
        }

        $request->validate([
            'amount' => 'required|integer|min:1',
            'description' => 'required|string|max:255',
        ]);

        DB::transaction(function () use ($request, $tenant, $shift) {
            Expense::create([
                'tenant_id' => $tenant->id,
                'shift_id' => $shift->id,
                'amount' => $request->amount,
                'description' => $request->description,
            ]);

            $shift->decrement('system_ending_cash', $request->amount);
        });

        return back()->with('success', 'Pengeluaran berhasil dicatat!');
    }

    /**
     * Mencatat pembayaran tambahan / cicilan (Split Bill).
     */
    public function recordPayment(Request $request, $orderId)
    {
        $request->validate([
            'payment_method' => 'required|string|in:cash,qris,transfer',
            'amount_paid' => 'required|integer|min:1',
        ]);

        $order = Order::findOrFail($orderId);

        // Gunakan OrderManagerService
        $service = app(\App\Services\OrderManagerService::class);
        $service->addPayment($order, [
            'payment_method' => $request->payment_method,
            'amount_paid' => $request->amount_paid,
        ]);

        // Jika cash, tambahkan ke kasir shift
        if ($request->payment_method === 'cash') {
            $tenant = app('tenant');
            $shift = Shift::withoutGlobalScope(\App\Tenant\TenantScope::class)
                ->where('tenant_id', $tenant->id)
                ->where('user_id', Auth::id())
                ->whereNull('closed_at')
                ->first();

            if ($shift) {
                $shift->increment('system_ending_cash', $request->amount_paid);
            }
        }

        // Kirim notifikasi event real-time via locomo.gateway
        $tenant = app('tenant');
        if ($tenant) {
            \App\Services\GatewayService::emit(
                'caffe-' . $tenant->id . '-kitchen',
                'order-updated',
                [
                    'id' => $order->id,
                    'status' => $order->kitchen_status,
                    'payment_status' => $order->payment_status,
                    'order_number' => $order->order_number,
                ]
            );
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Pembayaran berhasil dicatat!',
                'order' => $order->load('payments')
            ]);
        }

        return back()->with('success', 'Pembayaran berhasil dicatat!');
    }

    /**
     * Membatalkan item pesanan (Void).
     */
    public function voidOrderItem(Request $request, $itemId)
    {
        $orderItem = OrderItem::findOrFail($itemId);

        // Update status ke void. Ini akan mentrigger OrderItemObserver
        $orderItem->update([
            'status' => 'void'
        ]);

        return back()->with('success', 'Item pesanan berhasil dibatalkan (void)!');
    }

    /**
     * Mengirim pesanan ke dapur (Siapkan Pesanan).
     */
    public function prepareOrder(Request $request, $orderId)
    {
        $order = Order::findOrFail($orderId);
        
        $user = Auth::user();
        $roleLabel = str_replace('_', ' ', $user->role);
        if ($user->isOwner() && session('owner_acting_role')) {
            $roleLabel = session('owner_acting_role');
        }
        $processedByName = $user->name . ' (' . ucwords($roleLabel) . ')';

        $order->update([
            'kitchen_status' => 'cooking',
            'processed_by_user_id' => $user->id,
            'processed_by_name' => $processedByName,
        ]);

        // Kirim notifikasi event real-time via locomo.gateway
        $tenant = app('tenant');
        if ($tenant) {
            \App\Services\GatewayService::emit(
                'caffe-' . $tenant->id . '-kitchen',
                'new-order',
                [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'table_number' => $order->table ? $order->table->name : ($order->order_type === 'takeaway' ? 'Takeaway' : 'Counter'),
                    'status' => 'cooking',
                    'total_amount' => $order->total_amount,
                    'created_at' => $order->created_at->toIso8601String(),
                    'items' => $order->items->map(function ($item) {
                        return [
                            'name' => $item->product->name,
                            'qty' => $item->quantity,
                        ];
                    })->toArray()
                ]
            );
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Pesanan berhasil dikirim ke dapur!'
            ]);
        }

        return back()->with('success', 'Pesanan dikirim ke dapur!');
    }

    /**
     * Membatalkan seluruh pesanan (Void Order).
     */
    public function cancelOrder(Request $request, $orderId)
    {
        $order = Order::findOrFail($orderId);
        
        $user = Auth::user();
        $roleLabel = str_replace('_', ' ', $user->role);
        if ($user->isOwner() && session('owner_acting_role')) {
            $roleLabel = session('owner_acting_role');
        }
        $processedByName = $user->name . ' (' . ucwords($roleLabel) . ')';

        DB::transaction(function () use ($order, $user, $processedByName) {
            $order->update([
                'payment_status' => 'void',
                'kitchen_status' => 'void',
                'processed_by_user_id' => $user->id,
                'processed_by_name' => $processedByName,
            ]);
            
            // Return stock for all order items that are not already voided
            foreach ($order->items as $item) {
                if ($item->status !== 'void') {
                    $item->product()->increment('stock', $item->quantity);
                    $item->update(['status' => 'void']);
                }
            }
        });

        // Kirim notifikasi event real-time via locomo.gateway
        $tenant = app('tenant');
        if ($tenant) {
            \App\Services\GatewayService::emit(
                'caffe-' . $tenant->id . '-kitchen',
                'order-updated',
                [
                    'id' => $order->id,
                    'status' => 'void',
                    'payment_status' => 'void',
                    'order_number' => $order->order_number,
                ]
            );
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Pesanan berhasil dibatalkan!'
            ]);
        }

        return back()->with('success', 'Pesanan berhasil dibatalkan!');
    }

    /**
     * Memperbarui stok produk (Admin Gudang).
     */
    public function updateProductStock(Request $request, $productId)
    {
        $request->validate([
            'stock' => 'required|integer|min:0',
        ]);
        
        $product = \App\Models\Product::withoutGlobalScope(\App\Tenant\TenantScope::class)->findOrFail($productId);
        $product->update(['stock' => $request->stock]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Stok ' . $product->name . ' berhasil diperbarui!',
                'product' => $product
            ]);
        }
        return back()->with('success', 'Stok produk berhasil diperbarui!');
    }

    /**
     * Menandai pesanan selesai diantar oleh kurir.
     */
    public function deliverOrder(Request $request, $orderId)
    {
        $order = Order::findOrFail($orderId);
        $order->update(['kitchen_status' => 'delivered']);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Pesanan ' . $order->order_number . ' selesai diantar!'
            ]);
        }
        return back()->with('success', 'Pesanan selesai diantar!');
    }

    /**
     * Memperbarui pesanan (Kasir).
     */
    public function updateOrder(Request $request, $orderId)
    {
        $order = Order::withoutGlobalScope(\App\Tenant\TenantScope::class)->findOrFail($orderId);

        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|integer',
            'items.*.qty' => 'required|integer|min:1',
            'customer_name' => 'nullable|string|max:255',
            'order_type' => 'required|string',
            'table_id' => 'nullable|integer',
        ]);

        try {
            \DB::transaction(function () use ($order, $request) {
                // 1. Kembalikan stok lama
                foreach ($order->items as $item) {
                    if ($item->status !== 'void') {
                        $product = \App\Models\Product::withoutGlobalScope(\App\Tenant\TenantScope::class)->find($item->product_id);
                        if ($product) {
                            $product->increment('stock', $item->quantity);
                        }
                    }
                }

                // 2. Hapus detail pesanan lama
                $order->items()->delete();

                // 3. Masukkan item baru & potong stok baru
                $totalAmount = 0;
                foreach ($request->items as $itemData) {
                    $product = \App\Models\Product::withoutGlobalScope(\App\Tenant\TenantScope::class)->findOrFail($itemData['id']);
                    
                    if ($product->stock < $itemData['qty']) {
                        throw new \Exception("Stok untuk " . $product->name . " tidak mencukupi.");
                    }

                    $product->decrement('stock', $itemData['qty']);

                    $order->items()->create([
                        'product_id' => $product->id,
                        'quantity' => $itemData['qty'],
                        'price' => $product->price,
                        'status' => 'pending',
                    ]);

                    $totalAmount += $product->price * $itemData['qty'];
                }

                // 4. Update data order
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

                $order->update([
                    'customer_name' => $request->customer_name,
                    'order_type' => $request->order_type,
                    'table_id' => $request->order_type === 'dine_in' ? $request->table_id : null,
                    'total_amount' => $totalAmount,
                    'kitchen_status' => 'pending', // Reset ke pending agar koki tahu ada pembaruan
                    'processed_by_user_id' => $processedByUserId,
                    'processed_by_name' => $processedByName,
                ]);
            });

            // Kirim notifikasi event real-time via locomo.gateway
            $tenant = app('tenant');
            if ($tenant) {
                \App\Services\GatewayService::emit(
                    'caffe-' . $tenant->id . '-kitchen',
                    'order-updated',
                    [
                        'id' => $order->id,
                        'status' => 'pending',
                        'order_number' => $order->order_number,
                    ]
                );
            }

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Pesanan ' . $order->order_number . ' berhasil diperbarui!',
                    'order' => $order
                ]);
            }
            return back()->with('success', 'Pesanan berhasil diperbarui!');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 422);
            }
            return back()->withErrors([$e->getMessage()]);
        }
    }

    /**
     * Get orders data for real-time dashboard updates.
     */
    public function getOrdersData(Request $request)
    {
        $tenant = app('tenant');
        if (!$tenant) {
            return response()->json(['success' => false, 'message' => 'Tenant tidak ditemukan.'], 404);
        }

        $user = Auth::user();

        // Tentukan peran aktif (effective role)
        $actingRole = $user->role;
        if ($user->isOwner()) {
            $actingRole = session('owner_acting_role', 'owner');
        }

        // 1. Get all orders (Owner loads all 30 orders, other roles load their appropriate lists)
        $orders = Order::withoutGlobalScope(\App\Tenant\TenantScope::class)
            ->where('tenant_id', $tenant->id)
            ->with(['items.product', 'payments', 'table'])
            ->orderBy('created_at', 'desc')
            ->get();

        // 2. Get shift if cashier
        $shift = null;
        $salesTotal = 0;
        $expensesTotal = 0;
        $systemEndingCash = 0;
        
        $isCashierRole = ($actingRole === 'cashier' || $actingRole === 'owner');
        if ($isCashierRole) {
            $shift = Shift::withoutGlobalScope(\App\Tenant\TenantScope::class)
                ->where('tenant_id', $tenant->id)
                ->where('user_id', $user->id)
                ->whereNull('closed_at')
                ->first();
            if ($shift) {
                $salesTotal = Order::withoutGlobalScope(\App\Tenant\TenantScope::class)
                    ->where('tenant_id', $tenant->id)
                    ->where('shift_id', $shift->id)
                    ->sum('total_amount');
                $expensesTotal = Expense::withoutGlobalScope(\App\Tenant\TenantScope::class)
                    ->where('tenant_id', $tenant->id)
                    ->where('shift_id', $shift->id)
                    ->sum('amount');
                $systemEndingCash = $shift->starting_cash + $salesTotal - $expensesTotal;
            }
        }

        // 3. Chef count
        $preparedCount = 0;
        $pendingCount = 0;
        $isChefRole = ($actingRole === 'chef' || $actingRole === 'owner');
        if ($isChefRole) {
            $preparedCount = OrderItem::withoutGlobalScope(\App\Tenant\TenantScope::class)
                ->where('status', 'ready')
                ->whereHas('order', function ($query) use ($tenant) {
                    $query->withoutGlobalScope(\App\Tenant\TenantScope::class)->where('tenant_id', $tenant->id);
                })
                ->whereDate('updated_at', today())
                ->count();

            $pendingCount = OrderItem::withoutGlobalScope(\App\Tenant\TenantScope::class)
                ->whereIn('status', ['pending', 'cooking'])
                ->whereHas('order', function ($query) use ($tenant) {
                    $query->withoutGlobalScope(\App\Tenant\TenantScope::class)->where('tenant_id', $tenant->id);
                })
                ->count();
        }

        // 4. Waiter count
        $deliveredCount = 0;
        $isWaiterRole = ($actingRole === 'waiter' || $actingRole === 'owner');
        if ($isWaiterRole) {
            $deliveredCount = Order::withoutGlobalScope(\App\Tenant\TenantScope::class)
                ->where('tenant_id', $tenant->id)
                ->where('kitchen_status', 'delivered')
                ->whereDate('updated_at', today())
                ->count();
        }

        // 5. Courier count
        $pendingDeliveries = 0;
        $deliveredToday = 0;
        $isKurirRole = ($actingRole === 'kurir' || $actingRole === 'owner');
        if ($isKurirRole) {
            if ($actingRole === 'kurir') {
                $pendingDeliveries = $orders->filter(fn($o) => $o->kitchen_status === 'ready')->count();
                $deliveredToday = $orders->filter(fn($o) => $o->kitchen_status === 'delivered' && $o->updated_at->isToday())->count();
            } else {
                $pendingDeliveries = $orders->filter(fn($o) => $o->kitchen_status === 'ready' && ($o->order_source === 'online' || $o->delivery_address))->count();
                $deliveredToday = $orders->filter(fn($o) => $o->kitchen_status === 'delivered' && $o->updated_at->isToday())->count();
            }
        }

        return response()->json([
            'success' => true,
            'orders' => $orders->take(30), // Ambil 30 order untuk dashboard UI
            'shift' => $shift,
            'sales_total' => $salesTotal,
            'expenses_total' => $expensesTotal,
            'system_ending_cash' => $systemEndingCash,
            'prepared_count' => $preparedCount,
            'pending_count' => $pendingCount,
            'delivered_count' => $deliveredCount,
            'pending_deliveries' => $pendingDeliveries,
            'delivered_today' => $deliveredToday,
        ]);
    }

    /**
     * Mengubah peran kerja aktif Owner.
     */
    public function switchRole(Request $request)
    {
        $request->validate([
            'role' => 'required|in:owner,cashier,chef,admin_gudang,kurir'
        ]);

        if (Auth::user()->isOwner()) {
            session(['owner_acting_role' => $request->role]);
        }

        return redirect()->back();
    }

    /**
     * Helper untuk redirect user berdasarkan role.
     */
    private function redirectUser(User $user)
    {
        if ($user->isOwner()) {
            return redirect()->intended(route('owner.dashboard'));
        }

        // Staf kasir, chef, waiter diarahkan ke staff dashboard
        return redirect()->intended(route('staff.dashboard'));
    }
}
