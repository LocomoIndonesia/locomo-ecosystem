<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MerchantRegistrationController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/unimart/register-lead', [MerchantRegistrationController::class, 'registerLead']);
Route::get('/unimart/confirm-lead', [MerchantRegistrationController::class, 'confirmLead']);
Route::post('/unimart/verify-payment', [MerchantRegistrationController::class, 'verifyPayment']);
