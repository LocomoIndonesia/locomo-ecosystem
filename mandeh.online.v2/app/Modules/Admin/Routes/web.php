<?php

use App\Modules\Admin\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    Route::get('/moderation', [\App\Modules\Admin\Controllers\ModerationController::class, 'index'])->name('moderation.index');
    Route::post('/moderation/{id}/decide', [\App\Modules\Admin\Controllers\ModerationController::class, 'decide'])->name('moderation.decide');

    Route::get('/business-verification', [\App\Modules\Admin\Controllers\BusinessVerificationController::class, 'index'])->name('business.verification.index');
    Route::post('/business-verification/{id}/verify', [\App\Modules\Admin\Controllers\BusinessVerificationController::class, 'verify'])->name('business.verification.verify');
});
