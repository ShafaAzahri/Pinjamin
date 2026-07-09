<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CatalogController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\AdminController;

/*
|--------------------------------------------------------------------------
| API Routes — Pinjamin REST API
|--------------------------------------------------------------------------
| Base URL  : /api
| Auth Type : Laravel Sanctum (Bearer Token)
|
| All protected routes require the header:
|   Authorization: Bearer {token}
|--------------------------------------------------------------------------
*/

// ─── Public: Auth ────────────────────────────────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('/login',    [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
});

// ─── Protected Routes ────────────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me',      [AuthController::class, 'me']);

    // ─── Student Routes ───────────────────────────────────────────────
    Route::middleware('student')->group(function () {
        // Catalog
        Route::get('/catalog',           [CatalogController::class, 'index']);
        Route::get('/categories',        [CatalogController::class, 'categories']);
        Route::post('/cart/checkout',    [CatalogController::class, 'checkout']);

        // Loans
        Route::get('/loans',                         [StudentController::class, 'loans']);
        Route::get('/loans/{loan}',                  [StudentController::class, 'loanDetail']);
        Route::post('/loans/{loan}/return',          [StudentController::class, 'submitReturn']);

        // Fines
        Route::get('/fines',                         [StudentController::class, 'fines']);
        Route::post('/fines/{fine}/snap-token',      [StudentController::class, 'getSnapToken']);

        // Notifications
        Route::get('/notifications',                 [StudentController::class, 'notifications']);
        Route::get('/notifications/unread-count',    [StudentController::class, 'unreadCount']);
    });

    // ─── Admin Routes ─────────────────────────────────────────────────
    Route::middleware('admin')->prefix('admin')->group(function () {
        // Dashboard
        Route::get('/dashboard',                     [AdminController::class, 'dashboard']);

        // Inventory
        Route::get('/inventory',                     [AdminController::class, 'inventory']);

        // Loans
        Route::get('/loans',                         [AdminController::class, 'loans']);
        Route::post('/loans/{loan}/approve',         [AdminController::class, 'approveLoan']);
        Route::post('/loans/{loan}/reject',          [AdminController::class, 'rejectLoan']);
        Route::post('/loans/{loan}/verify-return',   [AdminController::class, 'verifyReturn']);

        // Fines
        Route::get('/fines',                         [AdminController::class, 'fines']);
        Route::post('/fines/{fine}/verify',          [AdminController::class, 'verifyFine']);

        // Users
        Route::get('/users/pending',                 [AdminController::class, 'pendingUsers']);
        Route::post('/users/{user}/verify',          [AdminController::class, 'verifyUser']);

        // Settings
        Route::get('/settings',                      [AdminController::class, 'settings']);
        Route::put('/settings',                      [AdminController::class, 'updateSettings']);
    });
});

