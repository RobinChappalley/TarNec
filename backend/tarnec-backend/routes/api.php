<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\StudentAuthController;
use App\Http\Controllers\Auth\StudentPasswordResetController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\LendingController;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

// Routes publiques
Route::post('student/register', [StudentAuthController::class, 'register']);
Route::post('student/login', [StudentAuthController::class, 'login']);
Route::post('student/forgot-password', [StudentPasswordResetController::class, 'forgotPassword']);
Route::post('student/reset-password', [StudentPasswordResetController::class, 'resetPassword']);

// Routes protégées (front-office)
Route::middleware('auth:sanctum')->group(function () {
    // Routes étudiant
    Route::prefix('student')->group(function () {
        Route::post('logout', [StudentAuthController::class, 'logout']);
        Route::get('me', [StudentAuthController::class, 'me']);
    });

    // Routes des articles (lecture seule pour les étudiants)
    Route::prefix('articles')->group(function () {
        Route::get('/', [ArticleController::class, 'index']);
        Route::get('/{article}', [ArticleController::class, 'show']);
    });

    // Routes des prêts (étudiants)
    Route::prefix('lendings')->group(function () {
        Route::post('/', [LendingController::class, 'store']);
        Route::get('/my-lendings', [LendingController::class, 'myLendings']);
        Route::get('/{lending}', [LendingController::class, 'show']);
    });
});

// Routes back-office (admin uniquement)
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    // Gestion des articles
    Route::prefix('articles')->group(function () {
        Route::post('/', [ArticleController::class, 'store']);
        Route::put('/{article}', [ArticleController::class, 'update']);
        Route::delete('/{article}', [ArticleController::class, 'destroy']);
    });

    // Gestion des prêts
    Route::prefix('lendings')->group(function () {
        Route::get('/', [LendingController::class, 'index']);
        Route::put('/{lending}', [LendingController::class, 'update']);
        Route::delete('/{lending}', [LendingController::class, 'destroy']);
    });
});
