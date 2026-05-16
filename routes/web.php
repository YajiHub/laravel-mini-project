<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\StockTransactionController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

// ============================================================
// PUBLIC ROUTES (No Authentication Required)
// ============================================================
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'authenticate'])->name('authenticate');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.store');
Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');

// ============================================================
// AUTHENTICATED ROUTES
// ============================================================
Route::middleware('auth')->group(function () {
    
    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::put('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');

    // Inventory Management - Inventory Manager & Admin only
    Route::middleware('role:inventory_manager|admin')->group(function () {
        Route::resource('products', ProductController::class);
        Route::resource('categories', CategoryController::class);
        Route::resource('suppliers', SupplierController::class);
    });

    // Stock Transactions - Inventory Manager & Admin
    Route::middleware('role:inventory_manager|admin')->group(function () {
        Route::get('/stock-transactions', [StockTransactionController::class, 'index'])->name('stock-transactions.index');
        Route::post('/stock-transactions', [StockTransactionController::class, 'store'])->name('stock-transactions.store');
    });

    // POS System - Cashier & Admin only
    Route::middleware('role:cashier|admin')->prefix('pos')->name('pos.')->group(function () {
        Route::get('/', [PosController::class, 'index'])->name('index');
        Route::post('/search', [PosController::class, 'search'])->name('search');
        Route::post('/add-to-cart', [PosController::class, 'addToCart'])->name('add-to-cart');
        Route::post('/remove-from-cart', [PosController::class, 'removeFromCart'])->name('remove-from-cart');
        Route::post('/update-quantity', [PosController::class, 'updateQuantity'])->name('update-quantity');
        Route::post('/checkout', [PosController::class, 'checkout'])->name('checkout');
        Route::get('/receipt/{transaction}', [PosController::class, 'receipt'])->name('receipt');
        Route::get('/receipt/{transaction}/pdf', [PosController::class, 'receiptPdf'])->name('receipt-pdf');
    });

    // Transaction History - Store Manager & Admin
    Route::middleware('role:store_manager|admin')->get('/pos/transactions', [PosController::class, 'transactions'])->name('pos.transactions');
});

// ============================================================
// ADMIN ROUTES
// ============================================================
Route::middleware('auth', 'role:admin')->prefix('admin')->name('admin.')->group(function () {
    
    // Users Management
    Route::resource('users', UserController::class);
    Route::patch('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
    
    // Audit Logs
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
    Route::get('/audit-logs/{auditLog}', [AuditLogController::class, 'show'])->name('audit-logs.show');

    // Settings
    // Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    // Route::post('/settings', [SettingsController::class, 'store'])->name('settings.store');
});

// ============================================================
// API ROUTES (Optional - for future use)
// ============================================================
// Include separate API routes in routes/api.php





Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
