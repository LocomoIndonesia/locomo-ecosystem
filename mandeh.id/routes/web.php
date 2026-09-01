<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SSOController;
use App\Http\Controllers\RegisterController;

Route::get('/', function () {
    return view('welcome');
});

// OpenID Connect & OAuth2 SSO Endpoints
Route::get('/.well-known/openid-configuration', [SSOController::class, 'discovery']);
Route::get('/oauth/jwks', [SSOController::class, 'jwks']);
Route::get('/oauth/authorize', [SSOController::class, 'authorizeForm']);
Route::post('/oauth/authorize', [SSOController::class, 'login'])->name('sso.login.submit');
Route::post('/oauth/token', [SSOController::class, 'token']);
Route::match(['get', 'post'], '/oauth/userinfo', [SSOController::class, 'userinfo']);

// Centralized SSO Registration
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('sso.register');
Route::post('/register', [RegisterController::class, 'register'])->name('sso.register.submit');
