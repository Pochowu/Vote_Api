<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\AdminController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Routes pour la gestion des admins
//Route::apiResource('admins', AdminController::class);


Route::prefix('payments')->group(function () {
    Route::post('/initiate', [PaymentController::class, 'initiatePayment']);
    Route::post('/callback', [PaymentController::class, 'paymentCallback']);
    Route::get('/status/{reference}', [PaymentController::class, 'checkPaymentStatus']);
    Route::post('/webhook', [PaymentController::class, 'webhookHandler']);
});
