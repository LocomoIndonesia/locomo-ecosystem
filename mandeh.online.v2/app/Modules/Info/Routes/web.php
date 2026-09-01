<?php

use App\Modules\Info\Controllers\InfoController;
use Illuminate\Support\Facades\Route;

Route::prefix('info')->group(function () {
    Route::get('/about', [InfoController::class, 'about'])->name('info.about');
    Route::get('/safety', [InfoController::class, 'safety'])->name('info.safety');
});
