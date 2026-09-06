<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Caffe\Controllers\CaffeLandingController;
use App\Http\Controllers\Owner\POSController;
use App\Http\Controllers\Owner\KDSController;
use App\Http\Controllers\Owner\TableController;

/*
|--------------------------------------------------------------------------
| Modul Caffe / F&B Routes
|--------------------------------------------------------------------------
| Seluruh alur khusus Food & Beverage: Meja, KDS Dapur, POS Kafe, dan QR Menu.
*/

Route::get('/caffe/{alias}/landing', [CaffeLandingController::class, 'showLanding'])->name('caffe.module.landing');
Route::get('/caffe/{alias}/menu-qr', [CaffeLandingController::class, 'showQRMenu'])->name('caffe.module.qr');
