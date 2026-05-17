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
use App\Http\Controllers\StoreSettingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

// Root redirect
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'authenticate'])->name('authenticate');

// Registration disabled — admin creates accounts via admin panel
Route::get('/register', function () {
    return redirect()->route('login')->with('info', 'Account registration is not available. Please contact your administrator.');
})->name('register');
Route::post('/register', function () {
    return redirect()->route('login');
})->name('register.store');

Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'reset'])->name('password.store');

// MFA verification
Route::get('/mfa-verify', [AuthController::class, 'showMfaVerify'])->name('mfa.verify');
Route::post('/mfa-verify', [AuthController::class, 'verifyMfa'])->name('mfa.verify.post');

// ============================================================
// AUTHENTICATED ROUTES
// ============================================================
Route::middleware(['auth', 'throttle:100,1'])->group(function () {

    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/poll', [NotificationController::class, 'poll'])->name('notifications.poll');
    Route::put('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');

    // Inventory Management - Inventory Manager & Admin only
    Route::middleware('role:inventory_manager|admin')->group(function () {
        Route::get('/products-import', [ProductController::class, 'showImport'])->name('products.import');
        Route::post('/products-import', [ProductController::class, 'import'])->name('products.import.post');
        Route::get('/products-export', [ProductController::class, 'exportExcel'])->name('products.export.excel');
        Route::resource('products', ProductController::class);
        Route::resource('categories', CategoryController::class);
        Route::resource('suppliers', SupplierController::class);
    });

    // Stock Transactions - Inventory Manager & Admin
    Route::middleware('role:inventory_manager|admin')->group(function () {
        Route::get('/stock-transactions', [StockTransactionController::class, 'index'])->name('stock-transactions.index');
        Route::post('/stock-transactions', [StockTransactionController::class, 'store'])->name('stock-transactions.store');
    });

    // POS System - Cashier & Admin only (terminal operations)
    Route::middleware('role:cashier|admin')->prefix('pos')->name('pos.')->group(function () {
        Route::get('/', [PosController::class, 'index'])->name('index');
        Route::get('/cart', [PosController::class, 'getCart'])->name('cart');
        Route::post('/search', [PosController::class, 'search'])->name('search');
        Route::post('/lookup-sku', [PosController::class, 'lookupSku'])->name('lookup-sku');
        Route::post('/add-to-cart', [PosController::class, 'addToCart'])->name('add-to-cart');
        Route::post('/remove-from-cart', [PosController::class, 'removeFromCart'])->name('remove-from-cart');
        Route::post('/update-quantity', [PosController::class, 'updateQuantity'])->name('update-quantity');
        Route::post('/clear-cart', [PosController::class, 'clearCart'])->name('clear-cart');
        Route::post('/checkout', [PosController::class, 'checkout'])->name('checkout');
        // Cashier's own sales history
        Route::get('/my-sales', [PosController::class, 'mySales'])->name('my-sales');
    });

    // Receipt view - any authenticated user (cashier sees after sale, manager views from history)
    Route::prefix('pos')->name('pos.')->group(function () {
        Route::get('/receipt/{transaction}', [PosController::class, 'receipt'])->name('receipt');
        Route::get('/receipt/{transaction}/pdf', [PosController::class, 'receiptPdf'])->name('receipt-pdf');
    });

    // Transaction History & Void - Store Manager & Admin
    Route::middleware('role:store_manager|admin')->group(function () {
        Route::get('/pos/transactions', [PosController::class, 'transactions'])->name('pos.transactions');
        Route::post('/pos/{transaction}/void', [PosController::class, 'void'])->name('pos.void');
    });

    // Store Settings - Store Manager & Admin
    Route::middleware('role:store_manager|admin')->group(function () {
        Route::get('/settings', [StoreSettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [StoreSettingController::class, 'update'])->name('settings.update');
        // Reports & Exports
        Route::get('/reports/inventory-pdf', [ReportController::class, 'inventoryPdf'])->name('reports.inventory-pdf');
        Route::get('/reports/inventory-csv', [ReportController::class, 'inventoryCsv'])->name('reports.inventory-csv');
        Route::get('/reports/sales-csv', [ReportController::class, 'salesCsv'])->name('reports.sales-csv');
        Route::get('/reports/sales-pdf', [ReportController::class, 'salesSummaryPdf'])->name('reports.sales-pdf');
        // Backup
        Route::post('/backup', [StoreSettingController::class, 'backup'])->name('backup.run');
    });
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
});

// Profile Routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/password', [ProfileController::class, 'updatePassword'])->name('password.update');
    Route::get('/profile/mfa', [ProfileController::class, 'setupMfa'])->name('profile.mfa');
    Route::post('/profile/mfa/enable', [ProfileController::class, 'enableMfa'])->name('profile.mfa.enable');
    Route::post('/profile/mfa/disable', [ProfileController::class, 'disableMfa'])->name('profile.mfa.disable');
    Route::post('/email/verification-notification', function () {
        return back()->with('status', 'verification-link-sent');
    })->middleware('throttle:6,1')->name('verification.send');
});
