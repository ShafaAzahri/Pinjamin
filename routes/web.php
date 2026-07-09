<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\LoanController as AdminLoanController;
use App\Http\Controllers\Admin\FineController as AdminFineController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Student\CatalogController;
use App\Http\Controllers\Student\LoanController as StudentLoanController;
use Illuminate\Support\Facades\Route;

// ─── Guest Routes ────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/', [AuthController::class, 'showLogin'])->name('home');
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// ─── Webhooks ──────────────────────────────────────────────
Route::post('/webhooks/midtrans', [\App\Http\Controllers\MidtransWebhookController::class, 'handle'])->name('midtrans.webhook');

// ─── Logout ──────────────────────────────────────────────
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// ─── Student Routes ──────────────────────────────────────
Route::middleware(['auth', 'student'])->group(function () {
    // Catalog & Cart
    Route::get('/catalog', [CatalogController::class, 'index'])->name('student.catalog');
    Route::post('/catalog/add-to-cart', [CatalogController::class, 'addToCart'])->name('student.cart.add');
    Route::post('/catalog/remove-from-cart', [CatalogController::class, 'removeFromCart'])->name('student.cart.remove');
    Route::get('/cart', [CatalogController::class, 'cart'])->name('student.cart');
    Route::post('/cart/checkout', [CatalogController::class, 'checkout'])->name('student.cart.checkout');

    // Loans
    Route::get('/loans', [StudentLoanController::class, 'index'])->name('student.loans');
    Route::get('/loans/{loan}', [StudentLoanController::class, 'show'])->name('student.loans.show');
    Route::post('/loans/{loan}/return', [StudentLoanController::class, 'submitReturn'])->name('student.loans.return');

    // Fines
    Route::get('/fines', [StudentLoanController::class, 'fines'])->name('student.fines');
    Route::post('/fines/{fine}/snap-token', [StudentLoanController::class, 'getSnapToken'])->name('student.fines.snap-token');

    // Notifications
    Route::get('/notifications', [StudentLoanController::class, 'notifications'])->name('student.notifications');
});

// ─── Admin Routes ────────────────────────────────────────
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/users/{id}/verify', [DashboardController::class, 'verifyUser'])->name('users.verify');

    // Inventory Management
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('/inventory/create', [InventoryController::class, 'create'])->name('inventory.create');
    Route::post('/inventory', [InventoryController::class, 'store'])->name('inventory.store');
    Route::get('/inventory/{item}', [InventoryController::class, 'show'])->name('inventory.show');
    Route::get('/inventory/{item}/edit', [InventoryController::class, 'edit'])->name('inventory.edit');
    Route::put('/inventory/{item}', [InventoryController::class, 'update'])->name('inventory.update');
    Route::delete('/inventory/{item}', [InventoryController::class, 'destroy'])->name('inventory.destroy');
    Route::post('/inventory/{item}/units', [InventoryController::class, 'addUnit'])->name('inventory.units.add');
    Route::put('/units/{unit}', [InventoryController::class, 'updateUnit'])->name('inventory.units.update');
    Route::delete('/units/{unit}', [InventoryController::class, 'deleteUnit'])->name('inventory.units.delete');

    // Categories
    Route::get('/categories', [InventoryController::class, 'categories'])->name('categories.index');
    Route::post('/categories', [InventoryController::class, 'storeCategory'])->name('categories.store');
    Route::put('/categories/{category}', [InventoryController::class, 'updateCategory'])->name('categories.update');
    Route::delete('/categories/{category}', [InventoryController::class, 'deleteCategory'])->name('categories.destroy');

    // Loans Management
    Route::get('/loans/report', [AdminLoanController::class, 'report'])->name('loans.report');
    Route::get('/loans', [AdminLoanController::class, 'index'])->name('loans.index');
    Route::get('/loans/{loan}', [AdminLoanController::class, 'show'])->name('loans.show');
    Route::post('/loans/{loan}/approve', [AdminLoanController::class, 'approve'])->name('loans.approve');
    Route::post('/loans/{loan}/reject', [AdminLoanController::class, 'reject'])->name('loans.reject');
    Route::post('/loans/{loan}/verify-return', [AdminLoanController::class, 'verifyReturn'])->name('loans.verify-return');

    // Fines Management
    Route::get('/fines', [AdminFineController::class, 'index'])->name('fines.index');
    Route::post('/fines/{fine}/verify', [AdminFineController::class, 'verifyPayment'])->name('fines.verify');

    // Settings
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
});
