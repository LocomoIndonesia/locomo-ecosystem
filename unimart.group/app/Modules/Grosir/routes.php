<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Grosir\Controllers\GrosirLandingController;
use App\Modules\Grosir\Controllers\GrosirPOSController;
use App\Modules\Grosir\Controllers\PriceTierController;

/*
|--------------------------------------------------------------------------
| Modul Grosir / Wholesale Routes
|--------------------------------------------------------------------------
| Seluruh alur khusus Grosiran: Tier Harga Kuantitas, POS Grosir, dan Landing Grosir.
*/

// Akses Publik & Kasir Grosir
Route::get('/grosir/{alias}', [GrosirLandingController::class, 'showLanding'])->name('grosir.landing');

Route::middleware(['auth'])->group(function () {
    Route::get('/grosir/{alias}/pos', [GrosirPOSController::class, 'showPOS'])->name('grosir.pos');
    Route::post('/grosir/{alias}/pos/checkout', [GrosirPOSController::class, 'checkout'])->name('grosir.pos.checkout');
});

// Akses Owner Grosir: Manajemen Tier Harga
Route::middleware(['auth', 'role:owner'])->group(function () {
    Route::get('/owner/price-tiers', [PriceTierController::class, 'index'])->name('owner.grosir.tiers.index');
    Route::post('/owner/price-tiers', [PriceTierController::class, 'store'])->name('owner.grosir.tiers.store');
    Route::delete('/owner/price-tiers/{id}', [PriceTierController::class, 'destroy'])->name('owner.grosir.tiers.destroy');
});
