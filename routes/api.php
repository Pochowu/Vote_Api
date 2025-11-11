<?php

use App\Http\Controllers\AdminController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EventController;
use App\Http\Controllers\CandidateController;
use App\Http\Controllers\VoteController;
use App\Http\Controllers\PaymentController;


// Authentification admin
Route::post('/login', [AdminController::class, 'login']);

// Liste publique des concours (événements)
Route::get('/events', [EventController::class, 'index']);
Route::get('/events/{id}', [EventController::class, 'show']);

// Liste des candidats d’un concours
Route::get('/events/{event_id}/candidates', [CandidateController::class, 'getByEvent']);
Route::get('/candidates/{id}', [CandidateController::class, 'show']);

// Enregistrement d’un vote (sans compte)
Route::post('/votes', [VoteController::class, 'store']);

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AdminController::class, 'logout']);

    // Gestion des concours
    Route::apiResource('/events', EventController::class);

    // Gestion des candidats
    Route::apiResource('/candidates', CandidateController::class);
    
    // Gestion des votes (consultation / stats)
    Route::get('/votes', [VoteController::class, 'index']);
    Route::get('/votes/statistics', [VoteController::class, 'statistics']);

});

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


