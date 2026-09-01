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

Route::get('/', function () {
    return view('welcome');
});

Route::get('/caffe/{alias}', function ($alias) {
    $tenant = app('tenant');
    if (!$tenant) {
        abort(404);
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
