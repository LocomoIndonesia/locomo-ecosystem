<?php

use App\Modules\TourismRegistration\Controllers\TourismRegistrationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web'])->group(function () {
    // Public registration (guest/visitor sign up)
    Route::get('/register', [TourismRegistrationController::class, 'showRegisterForm'])->name('register');
    // Overriding the POST route from main web.php
    Route::post('/register', [TourismRegistrationController::class, 'register'])->name('register.submit');

    // Authenticated onboarding
    Route::middleware(['auth'])->prefix('tourism')->group(function () {
        Route::get('/register', [TourismRegistrationController::class, 'showOnboardingForm'])->name('tourism.register');
        Route::post('/register', [TourismRegistrationController::class, 'submitOnboarding'])->name('tourism.register.submit');
    });
});
