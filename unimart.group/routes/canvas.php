<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Canvas\CanvasController;

/*
|--------------------------------------------------------------------------
| Canvas Module Routes
|--------------------------------------------------------------------------
| Schema Module: canvas
| Prefix: /canvas
| Architecture: Single Canvas Shell (SCS)
|
| Hanya 2 route halaman utama. Semua "navigasi" adalah state change
| di dalam shell yang dipicu oleh AJAX API routes di bawah.
|
| Halaman lama (/owner/... /staff/...) tidak diubah sama sekali.
|--------------------------------------------------------------------------
*/

Route::prefix('canvas')
    ->middleware(['auth', 'canvas.tenant'])
    ->name('canvas.')
    ->group(function () {

        // ─────────────────────────────────────────────────────────────
        // HALAMAN SHELL (2 Route Utama)
        // ─────────────────────────────────────────────────────────────

        // Single Canvas Shell — satu halaman untuk semua screen Owner & Staff
        Route::get('/', [CanvasController::class, 'shell'])->name('shell');

        // Kitchen Display System — terpisah karena mode full-screen display
        Route::get('/kds', [CanvasController::class, 'kds'])->name('kds');

        // ─────────────────────────────────────────────────────────────
        // API ROUTES (AJAX/JSON — dipanggil dari dalam shell)
        // ─────────────────────────────────────────────────────────────

        Route::prefix('api')->name('api.')->group(function () {

            // Command Parser — terima input cmd, return action JSON
            Route::post('/cmd', [CanvasController::class, 'command'])->name('cmd');

            // Layout Engine — ambil definisi layout JSON untuk screen tertentu
            Route::get('/layout', [CanvasController::class, 'getLayout'])->name('layout');

            // Widget Refresh — refresh data 1 widget spesifik
            Route::get('/widget', [CanvasController::class, 'getWidget'])->name('widget');

            // Help Panel — daftar command yang difilter berdasarkan role
            Route::get('/help', [CanvasController::class, 'getHelp'])->name('help');

            // ── Data APIs per Modul ──────────────────────────────────
            Route::get('/dashboard',     [CanvasController::class, 'getDashboard'])->name('dashboard');
            Route::get('/orders',        [CanvasController::class, 'getOrders'])->name('orders');
            Route::get('/menu',          [CanvasController::class, 'getMenu'])->name('menu');
            Route::get('/finance',       [CanvasController::class, 'getFinance'])->name('finance');
            Route::get('/team',          [CanvasController::class, 'getTeam'])->name('team');
            Route::get('/inventory',     [CanvasController::class, 'getInventory'])->name('inventory');
            Route::get('/suppliers',     [CanvasController::class, 'getSuppliers'])->name('suppliers');
            Route::get('/purchases',     [CanvasController::class, 'getPurchases'])->name('purchases');
            Route::get('/payables',      [CanvasController::class, 'getPayables'])->name('payables');
            Route::get('/receivables',   [CanvasController::class, 'getReceivables'])->name('receivables');
            Route::get('/tables',        [CanvasController::class, 'getTables'])->name('tables');
            Route::get('/settings',      [CanvasController::class, 'getSettings'])->name('settings');
            Route::get('/notifications', [CanvasController::class, 'getNotifications'])->name('notifications');

            // ── Mutasi Data (POST) ────────────────────────────────────
            Route::post('/pos/checkout',         [CanvasController::class, 'posCheckout'])->name('pos.checkout');
            Route::post('/orders/{id}/action',   [CanvasController::class, 'orderAction'])->name('orders.action');
            Route::post('/menu/save',            [CanvasController::class, 'menuSave'])->name('menu.save');
            Route::post('/menu/{id}/toggle',     [CanvasController::class, 'menuToggle'])->name('menu.toggle');
            Route::post('/shift/open',           [CanvasController::class, 'shiftOpen'])->name('shift.open');
            Route::post('/shift/close',          [CanvasController::class, 'shiftClose'])->name('shift.close');
            Route::post('/expense',              [CanvasController::class, 'expenseSave'])->name('expense');
            Route::post('/kds/{id}/complete',    [CanvasController::class, 'kdsComplete'])->name('kds.complete');
        });
    });
