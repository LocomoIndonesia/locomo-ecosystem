<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Marketplace\Controllers\MarketplaceHomeController;

/*
|--------------------------------------------------------------------------
| Modul Marketplace Routes
|--------------------------------------------------------------------------
| Seluruh alur agregasi publik marketplace Unimart: Direktori Toko,
| Pencarian Produk Lintas Toko, dan Etalase Marketplace Global.
*/

Route::get('/marketplace', [MarketplaceHomeController::class, 'index'])->name('marketplace.home');
