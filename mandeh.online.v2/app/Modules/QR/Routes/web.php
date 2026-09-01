<?php

use App\Modules\QR\Controllers\QrController;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    Route::get('/qr/{code}', [QrController::class, 'show'])->name('qr.landing');
});
