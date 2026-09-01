<?php

use App\Modules\Review\Controllers\ReviewController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/tourism')->group(function () {
    Route::post('/reviews', [ReviewController::class, 'store'])->name('api.reviews.store');
});
