<?php

namespace App\Http\Controllers\Canvas;

use App\Http\Controllers\Controller;
use App\Services\Canvas\CanvasCommandParser;
use App\Services\Canvas\CanvasLayoutService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use App\Models\Product;
use App\Models\Order;
use App\Models\Supplier;
use App\Models\Purchase;
use App\Models\AccountPayable;
use App\Models\AccountReceivable;
use App\Models\Table;
use App\Models\Staff;

/**
 * CanvasController
 *
 * Controller utama untuk Canvas Module (Single Canvas Shell Architecture).
 * Mengikuti pola "Skinny Controller, Fat Service" — semua logika bisnis
 * didelegasikan ke service layer yang sudah ada.
 *
 * Hanya ada 2 method yang me-render View:
 *   - shell() → Single Canvas Shell
 *   - kds()   → Kitchen Display System (full-screen)
 *
 * Semua method lain mengembalikan JsonResponse untuk dikonsumsi canvas.js.
 */
class CanvasController extends Controller
{
    public function __construct(
        protected CanvasLayoutService  $layoutService,
        protected CanvasCommandParser  $commandParser,
    ) {}

    // ─────────────────────────────────────────────────────────────────────
    // VIEW RENDERERS
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Render Single Canvas Shell.
     * Inject CANVAS_BOOT data ke view agar canvas.js bisa boot tanpa
     * round-trip API pertama.
     */
    public function shell(Request $request): View
    {
        $user   = $request->user();
        $tenant = $request->attributes->get('canvas_tenant');
        $role   = $request->attributes->get('canvas_role', 'staff');

        // Boot data yang di-embed ke window.CANVAS_BOOT di shell
        $canvasBoot = [
            'user' => [
                'id'     => $user->id,
                'name'   => $user->name,
                'email'  => $user->email,
                'role'   => $role,
                'avatar' => $user->avatar ?? null,
            ],
            'tenant' => [
                'id'            => $tenant->id,
                'name'          => $tenant->name,
                'alias'         => $tenant->domain,
                'business_type' => $tenant->business_type,
                'logo'          => $tenant->logo ?? null,
                'currency'      => 'IDR',
            ],
            'config' => [
                'api_base'          => '/canvas/api',
                'poll_interval_ms'  => 5000,   // live order polling interval
                'push_vapid_key'    => config('services.vapid.public_key', ''),
                'initial_screen'    => $this->getInitialScreen($role),
            ],
            'screens' => $this->layoutService->getAvailableScreens($role),
        ];

        return view('canvas.shell', compact('canvasBoot', 'user', 'tenant', 'role'));
    }

    /**
     * Render Kitchen Display System (halaman terpisah, full-screen).
     */
    public function kds(Request $request): View
    {
        $user   = $request->user();
        $tenant = $request->attributes->get('canvas_tenant');

        $orders = Order::where('tenant_id', $tenant->id)
            ->whereIn('status', ['pending', 'preparing'])
            ->with('items.product')
            ->orderBy('created_at', 'asc')
            ->get();

        return view('canvas.kds', compact('orders', 'tenant', 'user'));
    }

    // ─────────────────────────────────────────────────────────────────────
    // COMMAND BAR
    // ─────────────────────────────────────────────────────────────────────

    /**
     * POST /canvas/api/cmd
     * Terima input command dari user, return instruksi action JSON.
     */
    public function command(Request $request): JsonResponse
    {
        $input = trim($request->input('input', ''));
        $role  = $request->attributes->get('canvas_role', 'staff');

        if (empty($input)) {
            return response()->json([
                'ok'     => false,
                'action' => 'error',
                'message' => 'Perintah tidak boleh kosong.',
            ]);
        }

        $result = $this->commandParser->parse($input, $role);

        return response()->json($result);
    }

    // ─────────────────────────────────────────────────────────────────────
    // LAYOUT ENGINE
    // ─────────────────────────────────────────────────────────────────────

    /**
     * GET /canvas/api/layout?screen={screen}
     * Ambil definisi layout JSON untuk screen tertentu.
     */
    public function getLayout(Request $request): JsonResponse
    {
        $screen = $request->query('screen', 'home');
        $role   = $request->attributes->get('canvas_role', 'staff');

        $layout = $this->layoutService->getLayout($screen, $role);

        return response()->json($layout);
    }

    /**
     * GET /canvas/api/widget?id={widgetId}&screen={screen}
     * Refresh data untuk satu widget spesifik.
     */
    public function getWidget(Request $request): JsonResponse
    {
        $widgetId = $request->query('id');
        $screen   = $request->query('screen', 'home');
        $role     = $request->attributes->get('canvas_role', 'staff');
        $tenant   = $request->attributes->get('canvas_tenant');

        // Dapatkan data sesuai widget ID
        $data = match ($widgetId) {
            'daily_kpi'      => $this->buildDailyKpi($tenant),
            'activity_feed'  => $this->buildActivityFeed($tenant, 8),
            'top_products'   => $this->buildTopProducts($tenant, 5),
            'orders_live'    => $this->buildOrdersLive($tenant),
            default          => ['error' => "Widget '{$widgetId}' tidak dikenali."],
        };

        return response()->json([
            'widget_id' => $widgetId,
            'data'      => $data,
            'refreshed_at' => now()->toIso8601String(),
        ]);
    }

    /**
     * GET /canvas/api/help
     * Ambil daftar command untuk Help Panel, difilter berdasarkan role.
     */
    public function getHelp(Request $request): JsonResponse
    {
        $role = $request->attributes->get('canvas_role', 'staff');
        $help = $this->layoutService->getHelp($role);

        return response()->json($help);
    }

    // ─────────────────────────────────────────────────────────────────────
    // DATA APIs
    // ─────────────────────────────────────────────────────────────────────

    /** GET /canvas/api/dashboard */
    public function getDashboard(Request $request): JsonResponse
    {
        $tenant = $request->attributes->get('canvas_tenant');

        return response()->json([
            'daily_summary' => $this->buildDailyKpi($tenant),
            'weekly_trend'  => $this->buildWeeklyTrend($tenant),
            'top_products'  => $this->buildTopProducts($tenant, 5),
            'recent_activity' => $this->buildActivityFeed($tenant, 8),
        ]);
    }

    /** GET /canvas/api/orders?status={filter} */
    public function getOrders(Request $request): JsonResponse
    {
        $tenant = $request->attributes->get('canvas_tenant');
        $status = $request->query('status', 'all');

        $query = Order::where('tenant_id', $tenant->id)
            ->with(['items.product', 'table'])
            ->latest();

        if ($status !== 'all') {
            $query->where('status', $status);
        } else {
            $query->whereIn('status', ['pending', 'preparing', 'ready', 'served']);
        }

        $orders = $query->limit(50)->get()->map(fn($o) => $this->formatOrder($o));

        return response()->json([
            'orders' => $orders,
            'counts' => [
                'pending'   => Order::where('tenant_id', $tenant->id)->where('status', 'pending')->count(),
                'preparing' => Order::where('tenant_id', $tenant->id)->where('status', 'preparing')->count(),
                'ready'     => Order::where('tenant_id', $tenant->id)->where('status', 'ready')->count(),
                'served'    => Order::where('tenant_id', $tenant->id)->where('status', 'served')->count(),
            ],
            'fetched_at' => now()->toIso8601String(),
        ]);
    }

    /** GET /canvas/api/menu?category={cat}&search={q} */
    public function getMenu(Request $request): JsonResponse
    {
        $tenant   = $request->attributes->get('canvas_tenant');
        $category = $request->query('category');
        $search   = $request->query('search');

        $query = Product::where('tenant_id', $tenant->id)->orderBy('category')->orderBy('name');

        if ($category) {
            $query->where('category', $category);
        }

        if ($search) {
            $query->where('name', 'ilike', "%{$search}%");
        }

        $products = $query->get()->map(fn($p) => [
            'id'           => $p->id,
            'name'         => $p->name,
            'category'     => $p->category,
            'price'        => $p->price,
            'cost_price'   => $p->cost_price,
            'stock'        => $p->stock,
            'is_available' => $p->is_available,
            'image'        => $p->image ? asset('storage/' . $p->image) : null,
        ]);

        $categories = Product::where('tenant_id', $tenant->id)
            ->distinct()
            ->pluck('category')
            ->filter()
            ->values();

        return response()->json([
            'products'   => $products,
            'categories' => $categories,
        ]);
    }

    /** GET /canvas/api/finance?period={today|this_week|this_month|custom}&from={}&to={} */
    public function getFinance(Request $request): JsonResponse
    {
        $tenant = $request->attributes->get('canvas_tenant');
        $period = $request->query('period', 'today');

        [$from, $to] = $this->resolvePeriod($period, $request);

        // Delegate ke FinancialReportController logic via service (reuse yang sudah ada)
        $orders = Order::where('tenant_id', $tenant->id)
            ->where('status', 'completed')
            ->whereBetween('created_at', [$from->startOfDay(), $to->endOfDay()])
            ->with('items')
            ->get();

        $grossSales = $orders->sum('total_amount');
        $totalCogs  = $orders->flatMap->items->sum(fn($i) => $i->cost_price * $i->quantity);
        $grossProfit = $grossSales - $totalCogs;

        $expenses = \App\Models\Expense::where('tenant_id', $tenant->id)
            ->whereBetween('created_at', [$from->startOfDay(), $to->endOfDay()])
            ->sum('amount');

        return response()->json([
            'period'        => $period,
            'from'          => $from->toDateString(),
            'to'            => $to->toDateString(),
            'gross_sales'   => $grossSales,
            'total_cogs'    => $totalCogs,
            'gross_profit'  => $grossProfit,
            'expenses'      => $expenses,
            'net_profit'    => $grossProfit - $expenses,
            'order_count'   => $orders->count(),
            'avg_order'     => $orders->count() > 0 ? round($grossSales / $orders->count()) : 0,
        ]);
    }

    /** GET /canvas/api/team */
    public function getTeam(Request $request): JsonResponse
    {
        $tenant = $request->attributes->get('canvas_tenant');

        $staff = \App\Models\User::where('tenant_id', $tenant->id)
            ->where('role', '!=', 'owner')
            ->get()
            ->map(fn($u) => [
                'id'         => $u->id,
                'name'       => $u->name,
                'email'      => $u->email,
                'role'       => $u->role,
                'avatar'     => $u->avatar ?? null,
                'created_at' => $u->created_at?->diffForHumans(),
            ]);

        return response()->json(['staff' => $staff]);
    }

    /** GET /canvas/api/inventory?filter={all|low_stock|out_of_stock} */
    public function getInventory(Request $request): JsonResponse
    {
        $tenant = $request->attributes->get('canvas_tenant');
        $filter = $request->query('filter', 'all');

        $query = Product::where('tenant_id', $tenant->id);

        if ($filter === 'low_stock') {
            $query->where('stock', '<=', 5)->where('stock', '>', 0);
        } elseif ($filter === 'out_of_stock') {
            $query->where('stock', '<=', 0);
        }

        $products = $query->orderBy('stock')->get()->map(fn($p) => [
            'id'           => $p->id,
            'name'         => $p->name,
            'category'     => $p->category,
            'stock'        => $p->stock,
            'cost_price'   => $p->cost_price,
            'is_available' => $p->is_available,
            'stock_status' => $p->stock <= 0 ? 'out' : ($p->stock <= 5 ? 'low' : 'ok'),
        ]);

        return response()->json([
            'products' => $products,
            'summary'  => [
                'total'     => Product::where('tenant_id', $tenant->id)->count(),
                'low_stock' => Product::where('tenant_id', $tenant->id)->where('stock', '<=', 5)->where('stock', '>', 0)->count(),
                'out'       => Product::where('tenant_id', $tenant->id)->where('stock', '<=', 0)->count(),
            ],
        ]);
    }

    /** GET /canvas/api/suppliers */
    public function getSuppliers(Request $request): JsonResponse
    {
        $tenant    = $request->attributes->get('canvas_tenant');
        $suppliers = Supplier::where('tenant_id', $tenant->id)->get()->map(fn($s) => [
            'id'           => $s->id,
            'name'         => $s->name,
            'contact'      => $s->contact_name,
            'phone'        => $s->phone,
            'total_debt'   => AccountPayable::where('tenant_id', $tenant->id)
                ->where('supplier_id', $s->id)
                ->where('status', '!=', 'paid')
                ->sum('remaining_amount'),
        ]);

        return response()->json(['suppliers' => $suppliers]);
    }

    /** GET /canvas/api/purchases */
    public function getPurchases(Request $request): JsonResponse
    {
        $tenant    = $request->attributes->get('canvas_tenant');
        $purchases = Purchase::where('tenant_id', $tenant->id)
            ->with(['supplier', 'items.product'])
            ->latest()
            ->limit(30)
            ->get()
            ->map(fn($p) => [
                'id'           => $p->id,
                'date'         => $p->purchase_date,
                'supplier'     => $p->supplier?->name,
                'total'        => $p->total_amount,
                'payment_type' => $p->payment_type,
                'status'       => $p->status,
                'item_count'   => $p->items->count(),
            ]);

        return response()->json(['purchases' => $purchases]);
    }

    /** GET /canvas/api/payables */
    public function getPayables(Request $request): JsonResponse
    {
        $tenant   = $request->attributes->get('canvas_tenant');
        $payables = AccountPayable::where('tenant_id', $tenant->id)
            ->with('supplier')
            ->orderBy('due_date')
            ->get()
            ->map(fn($p) => [
                'id'               => $p->id,
                'supplier'         => $p->supplier?->name,
                'total'            => $p->total_amount,
                'paid'             => $p->paid_amount,
                'remaining'        => $p->remaining_amount,
                'due_date'         => $p->due_date,
                'status'           => $p->status,
                'is_overdue'       => $p->due_date && now()->gt($p->due_date) && $p->status !== 'paid',
            ]);

        return response()->json([
            'payables'     => $payables,
            'total_debt'   => $payables->sum('remaining'),
        ]);
    }

    /** GET /canvas/api/receivables */
    public function getReceivables(Request $request): JsonResponse
    {
        $tenant      = $request->attributes->get('canvas_tenant');
        $receivables = AccountReceivable::where('tenant_id', $tenant->id)
            ->with('customer')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($r) => [
                'id'        => $r->id,
                'customer'  => $r->customer?->name ?? $r->customer_name,
                'total'     => $r->total_amount,
                'paid'      => $r->paid_amount,
                'remaining' => $r->remaining_amount,
                'status'    => $r->status,
                'created'   => $r->created_at?->diffForHumans(),
            ]);

        return response()->json([
            'receivables'     => $receivables,
            'total_receivable' => $receivables->sum('remaining'),
        ]);
    }

    /** GET /canvas/api/tables */
    public function getTables(Request $request): JsonResponse
    {
        $tenant = $request->attributes->get('canvas_tenant');
        $tables = Table::where('tenant_id', $tenant->id)
            ->with('activeOrder')
            ->orderBy('number')
            ->get()
            ->map(fn($t) => [
                'id'           => $t->id,
                'number'       => $t->number,
                'name'         => $t->name ?? "Meja {$t->number}",
                'status'       => $t->status,
                'capacity'     => $t->capacity ?? 4,
                'qr_url'       => url("/caffe/{$tenant->domain}?table={$t->id}"),
                'active_order' => $t->activeOrder ? [
                    'id'    => $t->activeOrder->id,
                    'total' => $t->activeOrder->total_amount,
                ] : null,
            ]);

        return response()->json([
            'tables'    => $tables,
            'summary'   => [
                'total'     => $tables->count(),
                'available' => $tables->where('status', 'available')->count(),
                'occupied'  => $tables->where('status', 'occupied')->count(),
            ],
        ]);
    }

    /** GET /canvas/api/settings */
    public function getSettings(Request $request): JsonResponse
    {
        $tenant = $request->attributes->get('canvas_tenant');

        return response()->json([
            'store' => [
                'name'          => $tenant->name,
                'alias'         => $tenant->domain,
                'business_type' => $tenant->business_type,
                'phone'         => $tenant->phone,
                'address'       => $tenant->address,
                'tax_rate'      => $tenant->tax_rate ?? 0,
                'service_charge'=> $tenant->service_charge ?? 0,
                'receipt_footer'=> $tenant->receipt_footer ?? '',
            ],
        ]);
    }

    /** GET /canvas/api/notifications */
    public function getNotifications(Request $request): JsonResponse
    {
        // TODO: Implement push notification log model
        // Untuk sementara return empty array terstruktur
        return response()->json([
            'notifications' => [],
            'unread_count'  => 0,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // MUTASI DATA
    // ─────────────────────────────────────────────────────────────────────

    /** POST /canvas/api/pos/checkout */
    public function posCheckout(Request $request): JsonResponse
    {
        // Delegate ke POSController yang sudah ada
        $posController = app(\App\Http\Controllers\Owner\POSController::class);
        $tenant = $request->attributes->get('canvas_tenant');

        // Inject tenant alias ke request
        $request->route()->setParameter('alias', $tenant->domain);

        try {
            $response = $posController->checkout($request, $tenant->domain);
            return response()->json(['ok' => true, 'redirect' => false]);
        } catch (\Exception $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /** POST /canvas/api/orders/{id}/action */
    public function orderAction(Request $request, int $id): JsonResponse
    {
        $action = $request->input('action'); // prepare | deliver | cancel | complete | pay
        $tenant = $request->attributes->get('canvas_tenant');

        $order = Order::where('tenant_id', $tenant->id)->findOrFail($id);

        $statusMap = [
            'prepare'  => 'preparing',
            'deliver'  => 'served',
            'cancel'   => 'cancelled',
            'complete' => 'completed',
        ];

        if (!isset($statusMap[$action])) {
            return response()->json(['ok' => false, 'message' => 'Action tidak valid.'], 422);
        }

        $order->update(['status' => $statusMap[$action]]);

        return response()->json(['ok' => true, 'order_id' => $id, 'new_status' => $statusMap[$action]]);
    }

    /** POST /canvas/api/menu/save */
    public function menuSave(Request $request): JsonResponse
    {
        $tenant = $request->attributes->get('canvas_tenant');

        $validated = $request->validate([
            'id'           => 'nullable|integer',
            'name'         => 'required|string|max:255',
            'category'     => 'nullable|string|max:100',
            'price'        => 'required|numeric|min:0',
            'cost_price'   => 'nullable|numeric|min:0',
            'stock'        => 'nullable|integer|min:0',
            'is_available' => 'boolean',
        ]);

        $product = Product::updateOrCreate(
            ['id' => $validated['id'] ?? null, 'tenant_id' => $tenant->id],
            array_merge($validated, ['tenant_id' => $tenant->id])
        );

        return response()->json(['ok' => true, 'product' => $product]);
    }

    /** POST /canvas/api/menu/{id}/toggle — Toggle status tersedia/habis */
    public function menuToggle(Request $request, int $id): JsonResponse
    {
        $tenant  = $request->attributes->get('canvas_tenant');
        $product = Product::where('tenant_id', $tenant->id)->findOrFail($id);
        $product->update(['is_available' => !$product->is_available]);

        return response()->json([
            'ok'           => true,
            'product_id'   => $id,
            'is_available' => $product->is_available,
        ]);
    }

    /** POST /canvas/api/shift/open */
    public function shiftOpen(Request $request): JsonResponse
    {
        $staffController = app(\App\Http\Controllers\Auth\StaffAuthController::class);
        try {
            $staffController->startShift($request);
            return response()->json(['ok' => true, 'message' => 'Shift berhasil dibuka.']);
        } catch (\Exception $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /** POST /canvas/api/shift/close */
    public function shiftClose(Request $request): JsonResponse
    {
        $staffController = app(\App\Http\Controllers\Auth\StaffAuthController::class);
        try {
            $staffController->closeShift($request);
            return response()->json(['ok' => true, 'message' => 'Shift berhasil ditutup.']);
        } catch (\Exception $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /** POST /canvas/api/expense */
    public function expenseSave(Request $request): JsonResponse
    {
        $staffController = app(\App\Http\Controllers\Auth\StaffAuthController::class);
        try {
            $staffController->addExpense($request);
            return response()->json(['ok' => true, 'message' => 'Pengeluaran dicatat.']);
        } catch (\Exception $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /** POST /canvas/api/kds/{id}/complete */
    public function kdsComplete(Request $request, int $id): JsonResponse
    {
        $kdsController = app(\App\Http\Controllers\Owner\KDSController::class);
        $tenant = $request->attributes->get('canvas_tenant');
        try {
            $kdsController->completeOrder($request, $tenant->domain, $id);
            return response()->json(['ok' => true]);
        } catch (\Exception $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 422);
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    // HELPERS PRIVAT
    // ─────────────────────────────────────────────────────────────────────

    private function getInitialScreen(string $role): string
    {
        return match ($role) {
            'owner'   => 'home',
            'cashier' => 'pos',
            'chef'    => 'kds_redirect',
            'waiter'  => 'orders',
            default   => 'home',
        };
    }

    private function buildDailyKpi(mixed $tenant): array
    {
        $today = now()->startOfDay();

        $todayOrders = Order::where('tenant_id', $tenant->id)
            ->where('status', 'completed')
            ->whereDate('created_at', today())
            ->get();

        $pendingCount = Order::where('tenant_id', $tenant->id)
            ->whereIn('status', ['pending', 'preparing'])
            ->count();

        $activeStaff = \App\Models\ShiftLog::where('tenant_id', $tenant->id)
            ->whereNull('closed_at')
            ->count();

        return [
            'total_sales'    => $todayOrders->sum('total_amount'),
            'order_count'    => $todayOrders->count(),
            'orders_pending' => $pendingCount,
            'active_staff'   => $activeStaff,
            'avg_order'      => $todayOrders->count() > 0
                ? round($todayOrders->sum('total_amount') / $todayOrders->count())
                : 0,
        ];
    }

    private function buildWeeklyTrend(mixed $tenant): array
    {
        $trend = [];
        for ($i = 6; $i >= 0; $i--) {
            $date   = now()->subDays($i)->toDateString();
            $sales  = Order::where('tenant_id', $tenant->id)
                ->where('status', 'completed')
                ->whereDate('created_at', $date)
                ->sum('total_amount');
            $trend[] = ['date' => $date, 'sales' => $sales];
        }
        return $trend;
    }

    private function buildTopProducts(mixed $tenant, int $limit = 5): array
    {
        return \DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('orders.tenant_id', $tenant->id)
            ->where('orders.status', 'completed')
            ->whereDate('orders.created_at', '>=', now()->subDays(30))
            ->select('products.name', \DB::raw('SUM(order_items.quantity) as qty_sold'),
                     \DB::raw('SUM(order_items.subtotal) as revenue'))
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('qty_sold')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    private function buildActivityFeed(mixed $tenant, int $limit = 8): array
    {
        return Order::where('tenant_id', $tenant->id)
            ->with('table')
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn($o) => [
                'type'    => 'order',
                'icon'    => $this->statusIcon($o->status),
                'label'   => "Pesanan #{$o->id}" . ($o->table ? " — {$o->table->name}" : ''),
                'amount'  => $o->total_amount,
                'status'  => $o->status,
                'time'    => $o->created_at?->diffForHumans(),
            ])
            ->toArray();
    }

    private function buildOrdersLive(mixed $tenant): array
    {
        return Order::where('tenant_id', $tenant->id)
            ->whereIn('status', ['pending', 'preparing', 'ready'])
            ->count();
    }

    private function formatOrder(Order $order): array
    {
        return [
            'id'         => $order->id,
            'table'      => $order->table?->name ?? 'Take Away',
            'status'     => $order->status,
            'total'      => $order->total_amount,
            'item_count' => $order->items->count(),
            'items'      => $order->items->map(fn($i) => [
                'name' => $i->product?->name,
                'qty'  => $i->quantity,
                'note' => $i->note,
            ]),
            'created'    => $order->created_at?->diffForHumans(),
            'duration_minutes' => $order->created_at
                ? now()->diffInMinutes($order->created_at)
                : 0,
        ];
    }

    private function statusIcon(string $status): string
    {
        return match ($status) {
            'pending'   => '🟡',
            'preparing' => '🔵',
            'ready'     => '🟢',
            'served'    => '✅',
            'completed' => '✅',
            'cancelled' => '❌',
            default     => '⚪',
        };
    }

    private function resolvePeriod(string $period, Request $request): array
    {
        return match ($period) {
            'today'      => [now(), now()],
            'this_week'  => [now()->startOfWeek(), now()->endOfWeek()],
            'this_month' => [now()->startOfMonth(), now()->endOfMonth()],
            'custom'     => [
                \Carbon\Carbon::parse($request->query('from', now()->subDays(7))),
                \Carbon\Carbon::parse($request->query('to', now())),
            ],
            default      => [now(), now()],
        };
    }
}
