<?php

use App\Modules\Report\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'web'])->prefix('report')->group(function () {
    Route::get('/create', [ReportController::class, 'create'])->name('report.create');
    Route::post('/', [ReportController::class, 'store'])->name('report.store');
});
