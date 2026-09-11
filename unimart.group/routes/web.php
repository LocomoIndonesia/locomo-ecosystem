<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\StaffAuthController;
use App\Http\Controllers\Auth\OwnerAuthController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Owner\DashboardController;
use App\Http\Controllers\Owner\POSController;
use App\Http\Controllers\Owner\KDSController;
use App\Http\Controllers\Owner\MenuController;
use App\Http\Controllers\Owner\TableController;
use App\Http\Controllers\Owner\SupplierController;
use App\Http\Controllers\Owner\PurchaseController;
use App\Http\Controllers\Owner\AccountPayableController;
use App\Http\Controllers\Owner\AccountReceivableController;
use App\Http\Controllers\Owner\FinancialReportController;

Route::get('/', function (\Illuminate\Http\Request $request) {
    if (app()->bound('is_marketplace') && app('is_marketplace')) {
        return app(\App\Modules\Marketplace\Controllers\MarketplaceHomeController::class)->index($request);
    }

    $isRootDomain = app()->bound('is_root_domain') ? app('is_root_domain') : true;

    // Jika diakses dari subdomain merchant ({alias}.unimart.group.localhost / {alias}.unimart.group)
    if (!$isRootDomain) {
        $tenant = app()->bound('tenant') ? app('tenant') : null;
        if ($tenant) {
            if ($tenant->business_type === 'grosir') {
                return app(\App\Modules\Grosir\Controllers\GrosirLandingController::class)->showLanding($tenant->domain);
            } elseif ($tenant->business_type === 'caffe') {
                return app(\App\Modules\Caffe\Controllers\CaffeLandingController::class)->showLanding($tenant->domain);
            }
        }
        abort(404, 'Toko atau Merchant tidak ditemukan.');
    }

    // Jika diakses dari domain root (unimart.group.localhost / unimart.group / localhost), selalu tampilkan SaaS landing page
    return view('welcome');
});

// Path-Based Multi-Tenant Store Route (/s/{domain}) - Bekerja tanpa ketergantungan wildcard DNS/SSL
Route::prefix('s/{domain}')->group(function () {
    Route::get('/', function ($domain) {
        $tenant = \App\Models\Tenant::where('domain', $domain)->firstOrFail();
        app()->instance('tenant', $tenant);

        if ($tenant->business_type === 'grosir') {
            return app(\App\Modules\Grosir\Controllers\GrosirLandingController::class)->showLanding($tenant->domain);
        }
        return app(\App\Modules\Caffe\Controllers\CaffeLandingController::class)->showLanding($tenant->domain);
    })->name('store.landing');

    Route::get('/menu', function ($domain) {
        $tenant = \App\Models\Tenant::where('domain', $domain)->firstOrFail();
        app()->instance('tenant', $tenant);
        $products = \App\Models\Product::where('tenant_id', $tenant->id)->get();
        $tables = \App\Models\Table::where('tenant_id', $tenant->id)
            ->where('status', 'available')
            ->get();
        return view('caffe-qr', ['alias' => $domain, 'tenant' => $tenant, 'products' => $products, 'tables' => $tables]);
    })->name('store.menu');

    Route::post('/checkout', [POSController::class, 'customerCheckout'])->name('store.checkout');
});

Route::get('/caffe/{alias}', function ($alias) {
    $tenant = app()->bound('tenant') ? app('tenant') : null;
    if (!$tenant) {
        abort(404, 'Kafe tidak ditemukan');
    }
    $products = \App\Models\Product::where('tenant_id', $tenant->id)->get();
    $tables = \App\Models\Table::where('tenant_id', $tenant->id)
        ->where('status', 'available')
        ->get();
    return view('caffe-qr', compact('alias', 'tenant', 'products', 'tables'));
})->name('caffe.qr');

Route::post('/caffe/{alias}/checkout', [POSController::class, 'customerCheckout'])->name('caffe.checkout');

Route::get('/hotspot/{alias}', function ($alias) {
    return view('izone-hotspot', ['alias' => $alias]);
})->name('hotspot.captive');

Route::get('/login', [StaffAuthController::class, 'showLogin'])->name('login');
Route::post('/login', [StaffAuthController::class, 'login']);
Route::post('/logout', [StaffAuthController::class, 'logout'])->name('logout');
Route::get('/logout', [StaffAuthController::class, 'logout']);

Route::middleware('auth')->group(function () {
    Route::get('/change-password', [StaffAuthController::class, 'showChangePassword'])->name('password.change');
    Route::post('/change-password', [StaffAuthController::class, 'changePassword']);
    
    // Rute Staff Dashboard & Operasional
    Route::get('/staff/dashboard', [StaffAuthController::class, 'showDashboard'])->name('staff.dashboard');
    Route::post('/staff/switch-role', [StaffAuthController::class, 'switchRole'])->name('staff.switch-role');
    Route::post('/staff/shift/start', [StaffAuthController::class, 'startShift']);
    Route::post('/staff/shift/close', [StaffAuthController::class, 'closeShift']);
    Route::post('/staff/expense', [StaffAuthController::class, 'addExpense']);
    Route::post('/staff/order/{id}/payment', [StaffAuthController::class, 'recordPayment']);
    Route::post('/staff/order-item/{id}/void', [StaffAuthController::class, 'voidOrderItem']);
    Route::post('/staff/order/{id}/prepare', [StaffAuthController::class, 'prepareOrder']);
    Route::post('/staff/order/{id}/cancel', [StaffAuthController::class, 'cancelOrder']);
    Route::post('/staff/product/{id}/update-stock', [StaffAuthController::class, 'updateProductStock']);
    Route::post('/staff/order/{id}/deliver', [StaffAuthController::class, 'deliverOrder']);
    Route::post('/staff/order/{id}/update', [StaffAuthController::class, 'updateOrder']);
    Route::get('/staff/orders-data', [StaffAuthController::class, 'getOrdersData']);
});

// Rute Google OAuth
Route::get('/auth/google/redirect', [GoogleAuthController::class, 'redirectToGoogle'])->name('auth.google.redirect');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');

// Rute Owner (Pendaftaran & Otentikasi)
Route::middleware('guest')->group(function () {
    Route::get('/owner/register', [OwnerAuthController::class, 'showRegister'])->name('owner.register');
    Route::post('/owner/register', [OwnerAuthController::class, 'register']);
    Route::get('/owner/login', [OwnerAuthController::class, 'showLogin'])->name('owner.login');
    Route::post('/owner/login', [OwnerAuthController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('/owner/logout', [OwnerAuthController::class, 'logout'])->name('owner.logout');
});

// Group Khusus Owner
Route::middleware(['auth', 'role:owner'])->group(function () {
    Route::get('/owner/dashboard', [DashboardController::class, 'index'])->name('owner.dashboard');
    Route::get('/owner/team', [DashboardController::class, 'team'])->name('owner.team');
    Route::post('/owner/team/add', [DashboardController::class, 'addStaff'])->name('owner.team.add');

    // Rute CRUD Menu
    Route::get('/owner/menus', [MenuController::class, 'index'])->name('owner.menus');
    Route::post('/owner/menus/store', [MenuController::class, 'store'])->name('owner.menus.store');
    Route::post('/owner/menus/update/{id}', [MenuController::class, 'update'])->name('owner.menus.update');
    Route::post('/owner/menus/delete/{id}', [MenuController::class, 'destroy'])->name('owner.menus.delete');

    // Rute Manajemen Meja & QR
    Route::get('/owner/tables', [TableController::class, 'index'])->name('owner.tables');
    Route::post('/owner/tables/update', [TableController::class, 'updateTablesCount'])->name('owner.tables.update');
    Route::post('/owner/settings/update', [DashboardController::class, 'updateSettings'])->name('owner.settings.update');

    // Rute Manajemen Supplier
    Route::get('/owner/suppliers', [SupplierController::class, 'index'])->name('owner.suppliers.index');
    Route::post('/owner/suppliers', [SupplierController::class, 'store'])->name('owner.suppliers.store');
    Route::post('/owner/suppliers/{id}', [SupplierController::class, 'update'])->name('owner.suppliers.update');
    Route::delete('/owner/suppliers/{id}', [SupplierController::class, 'destroy'])->name('owner.suppliers.destroy');

    // Rute Pengadaan / Kulakan Stok (Purchases)
    Route::get('/owner/purchases', [PurchaseController::class, 'index'])->name('owner.purchases.index');
    Route::get('/owner/purchases/create', [PurchaseController::class, 'create'])->name('owner.purchases.create');
    Route::post('/owner/purchases', [PurchaseController::class, 'store'])->name('owner.purchases.store');

    // Rute Buku Utang Toko ke Supplier (Accounts Payable)
    Route::get('/owner/payables', [AccountPayableController::class, 'index'])->name('owner.payables.index');
    Route::post('/owner/payables/{id}/payment', [AccountPayableController::class, 'recordPayment'])->name('owner.payables.payment');

    // Rute Buku Piutang Pelanggan (Accounts Receivable)
    Route::get('/owner/receivables', [AccountReceivableController::class, 'index'])->name('owner.receivables.index');
    Route::post('/owner/receivables/{id}/payment', [AccountReceivableController::class, 'recordPayment'])->name('owner.receivables.payment');

    // Rute Laporan Keuangan (Laba Rugi & Arus Kas)
    Route::get('/owner/reports/finance', [FinancialReportController::class, 'finance'])->name('owner.reports.finance');
});

// Group Owner & Kasir
Route::middleware(['auth', 'role:owner,cashier'])->group(function () {
    Route::get('/caffe/{alias}/pos', [POSController::class, 'showPOS'])->name('owner.pos');
    Route::post('/caffe/{alias}/pos/checkout', [POSController::class, 'checkout'])->name('owner.pos.checkout');
});

// Group Owner & Chef/Barista
Route::middleware(['auth', 'role:owner,chef'])->group(function () {
    Route::get('/caffe/{alias}/kds', [KDSController::class, 'showKDS'])->name('owner.kds');
    Route::post('/caffe/{alias}/kds/complete/{order_id}', [KDSController::class, 'completeOrder'])->name('owner.kds.complete');
});
