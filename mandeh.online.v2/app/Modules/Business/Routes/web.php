<?php

use App\Modules\Business\Controllers\BusinessController;
use Illuminate\Support\Facades\Route;

Route::prefix('services')->group(function () {
    Route::get('/', [BusinessController::class, 'index'])->name('business.index');
    Route::get('/{id}', [BusinessController::class, 'show'])->name('business.show');
});

Route::get('/p/{slug}', [BusinessController::class, 'showBySlug'])->name('business.profile');
Route::get('/p/{slug}/menu', [BusinessController::class, 'menu'])->name('business.menu');
Route::get('/p/{slug}/products', [BusinessController::class, 'products'])->name('business.products');

Route::middleware(['web', 'auth'])->group(function () {
    Route::post('/services/register', [BusinessController::class, 'store'])->name('business.store');
    Route::get('/dashboard/upline/business', [BusinessController::class, 'uplineBusiness'])->name('business.upline');
    Route::get('/services/{id}/qr/print', [BusinessController::class, 'printQr'])->name('business.qr.print');
});
