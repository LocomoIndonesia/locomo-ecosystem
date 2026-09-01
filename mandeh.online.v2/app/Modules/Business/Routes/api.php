<?php

use App\Modules\Business\Controllers\BusinessController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/tourism')->group(function () {
    Route::get('/businesses', [BusinessController::class, 'index'])->name('api.businesses.index');
});
