<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\SSOClientController;

Route::get('/', function () {
    return view('welcome');
});

use App\Http\Controllers\Auth\LocalAuthController;

// Local Authentication Routes
Route::get('/login', [LocalAuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LocalAuthController::class, 'login']);
// Route::get('/register', [LocalAuthController::class, 'showRegisterForm'])->name('register');
// Route::post('/register', [LocalAuthController::class, 'register']);
Route::match(['get', 'post'], '/check-uniqueness', [LocalAuthController::class, 'checkUniqueness'])->name('check-uniqueness');
Route::get('/api/wilayah/provinces', [LocalAuthController::class, 'getProvinces'])->name('api.wilayah.provinces');
Route::get('/api/wilayah/regencies/{provinceId}', [LocalAuthController::class, 'getRegencies'])->name('api.wilayah.regencies');
Route::get('/api/wilayah/districts/{regencyId}', [LocalAuthController::class, 'getDistricts'])->name('api.wilayah.districts');
Route::get('/api/wilayah/villages/{districtId}', [LocalAuthController::class, 'getVillages'])->name('api.wilayah.villages');
Route::post('/logout', [LocalAuthController::class, 'logout'])->name('logout');

// Legacy Entity Show Route
Route::get('/v/{slug}', [\App\Http\Controllers\EntityController::class, 'show'])->name('entity.show');

// Destination Routes
Route::post('/destinasi/store', [\App\Http\Controllers\DestinationController::class, 'store'])->name('destinasi.store')->middleware('auth');

// UI Prototypes Group
Route::prefix('prototypes')->name('prototypes.')->group(function () {
    Route::get('/', function () { return view('prototypes.index'); })->name('index');
    Route::get('/landing', function () { return view('prototypes.landing'); })->name('landing');
    Route::get('/login', function () { return view('prototypes.login'); })->name('login');
    Route::get('/register-user', function () { return redirect()->route('login'); })->name('register-user');
    Route::get('/register-business', function () { return redirect()->route('login'); })->name('register-business');
    Route::get('/register-partner', function () { return redirect()->route('login'); })->name('register-partner');
    Route::get('/profile-restaurant', function () { return view('prototypes.profile-restaurant'); })->name('profile-restaurant');
    Route::get('/profile-wisma', function () { return view('prototypes.profile-wisma'); })->name('profile-wisma');
    Route::get('/scanner', function () { return view('prototypes.scanner'); })->name('scanner');
    Route::get('/wifi', function () { return view('prototypes.wifi'); })->name('wifi');
    Route::get('/destination', function () { return view('prototypes.destination'); })->name('destination');
    Route::get('/report', function () { return view('prototypes.report'); })->name('report');
    Route::get('/menu-qrcode', function () { return view('prototypes.menu-qrcode'); })->name('menu-qrcode');
    Route::get('/product-qrcode', function () { return view('prototypes.product-qrcode'); })->name('product-qrcode');
});

// Role-Specific Dashboards (Views Only - Data via API)
Route::prefix('dashboard')->group(function () {
    Route::get('/warga', [\App\Http\Controllers\Dashboard\WargaDashboardController::class, 'index'])->name('dashboard.warga');
    Route::get('/wisatawan', [\App\Http\Controllers\Dashboard\WisatawanDashboardController::class, 'index'])->name('dashboard.wisatawan');
    Route::get('/business', [\App\Http\Controllers\Dashboard\BusinessOwnerDashboardController::class, 'index'])->name('dashboard.business');
    Route::get('/layanan-publik', [\App\Http\Controllers\Dashboard\LayananPublikDashboardController::class, 'index'])->name('dashboard.layanan_publik');
    Route::get('/sponsor', [\App\Http\Controllers\Dashboard\SponsorDashboardController::class, 'index'])->name('dashboard.sponsor');
});

Route::get('/dashboard', function () {
    $user = auth()->user();
    if (!$user) return redirect()->route('login');
    
    if ($user->vanguardParticipant && $user->vanguardParticipant->is_hpi_candidate) {
        return redirect()->route('vanguard.dashboard');
    }
    
    switch ($user->role) {
        case 'warga':
            return redirect()->route('dashboard.warga');
        case 'wisatawan':
            return redirect()->route('dashboard.wisatawan');
        case 'business_owner':
            return redirect()->route('dashboard.business');
        case 'layanan_publik':
            return redirect()->route('dashboard.layanan_publik');
        case 'sponsor':
            return redirect()->route('dashboard.sponsor');
        case 'admin':
        case 'admin_manager':
            return redirect()->route('vanguard.admin.surveys');
        default:
            return redirect('/');
    }
})->name('dashboard')->middleware('auth');
