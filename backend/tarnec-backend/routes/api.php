<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\StudentAuthController;
use App\Http\Controllers\Auth\StudentPasswordResetController;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

// Routes d'authentification des étudiants
Route::prefix('student')->group(function () {
    Route::post('register', [StudentAuthController::class, 'register']);
    Route::post('login', [StudentAuthController::class, 'login']);
    
    // Routes de réinitialisation de mot de passe
    Route::post('forgot-password', [StudentPasswordResetController::class, 'forgotPassword']);
    Route::post('reset-password', [StudentPasswordResetController::class, 'resetPassword']);
    
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [StudentAuthController::class, 'logout']);
        Route::get('me', [StudentAuthController::class, 'me']);
    });
});
