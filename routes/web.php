<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin;
use App\Http\Controllers\StoreOwner;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InstallerController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\StoreController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Installer Routes
|--------------------------------------------------------------------------
*/

Route::prefix('install')->group(function () {
    Route::get('/', [InstallerController::class, 'index'])->name('installer.index');
    Route::get('/requirements', [InstallerController::class, 'requirements'])->name('installer.requirements');
    Route::get('/database', [InstallerController::class, 'database'])->name('installer.database');
    Route::post('/database', [InstallerController::class, 'databaseStore'])->name('installer.database.store');
    Route::get('/migrations', [InstallerController::class, 'migrations'])->name('installer.migrations');
    Route::post('/migrations', [InstallerController::class, 'migrationsRun'])->name('installer.migrations.run');
    Route::get('/admin', [InstallerController::class, 'admin'])->name('installer.admin');
    Route::post('/admin', [InstallerController::class, 'adminStore'])->name('installer.admin.store');
    Route::get('/complete', [InstallerController::class, 'complete'])->name('installer.complete');
});

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');

// Store public pages
Route::get('/store/{slug}', [StoreController::class, 'show'])->name('store.show');
Route::get('/store/{slug}/category/{categorySlug}', [StoreController::class, 'category'])->name('store.category');
Route::get('/store/{slug}/product/{productSlug}', [StoreController::class, 'product'])->name('store.product');

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

/*
|--------------------------------------------------------------------------
| Cart Routes (Accessible to guests and authenticated users)
|--------------------------------------------------------------------------
*/

Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::patch('/cart/{cartItem}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/{cartItem}', [CartController::class, 'remove'])->name('cart.remove');
Route::delete('/cart', [CartController::class, 'clear'])->name('cart.clear');

/*
|--------------------------------------------------------------------------
| Checkout Routes
|--------------------------------------------------------------------------
*/

Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout', [CheckoutController::class, 'process'])->name('checkout.process');
Route::get('/order/{order}/confirmation', [CheckoutController::class, 'confirmation'])->name('order.confirmation');

Route::middleware('auth')->group(function () {
    // Customer orders
    Route::get('/my-orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/my-orders/{order}', [OrderController::class, 'show'])->name('orders.show');
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/dashboard', [Admin\DashboardController::class, 'index'])->name('dashboard');

    // Store management
    Route::resource('stores', Admin\StoreController::class);
    Route::post('/stores/{store}/toggle-status', [Admin\StoreController::class, 'toggleStatus'])->name('stores.toggle-status');

    // User management
    Route::resource('users', Admin\UserController::class);
    Route::post('/users/{user}/toggle-status', [Admin\UserController::class, 'toggleStatus'])->name('users.toggle-status');

    // Payment settings
    Route::get('/settings/payment', [Admin\PaymentSettingController::class, 'index'])->name('settings.payment');
    Route::post('/settings/payment', [Admin\PaymentSettingController::class, 'update'])->name('settings.payment.update');

    // Reports
    Route::get('/reports/sales', [Admin\ReportController::class, 'sales'])->name('reports.sales');
    Route::get('/reports/orders', [Admin\ReportController::class, 'orders'])->name('reports.orders');
});

/*
|--------------------------------------------------------------------------
| Store Owner Routes
|--------------------------------------------------------------------------
*/

Route::prefix('store-owner')->name('store-owner.')->middleware(['auth', 'role:store_owner'])->group(function () {
    Route::get('/dashboard', [StoreOwner\DashboardController::class, 'index'])->name('dashboard');

    // Category management
    Route::resource('categories', StoreOwner\CategoryController::class)->except(['show']);

    // Product management
    Route::resource('products', StoreOwner\ProductController::class);
    Route::post('/products/{product}/update-stock', [StoreOwner\ProductController::class, 'updateStock'])->name('products.update-stock');

    // Order management
    Route::get('/orders', [StoreOwner\OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [StoreOwner\OrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}/status', [StoreOwner\OrderController::class, 'updateStatus'])->name('orders.update-status');
    Route::get('/orders/{order}/receipt', [StoreOwner\OrderController::class, 'receipt'])->name('orders.receipt');

    // POS
    Route::get('/pos', [StoreOwner\POSController::class, 'index'])->name('pos.index');
    Route::post('/pos/process', [StoreOwner\POSController::class, 'process'])->name('pos.process');
    Route::post('/pos/scan', [StoreOwner\POSController::class, 'scan'])->name('pos.scan');
    Route::post('/pos/{order}/mark-paid', [StoreOwner\POSController::class, 'markPaid'])->name('pos.mark-paid');

    // Store settings
    Route::get('/settings', [StoreOwner\StoreSettingController::class, 'index'])->name('settings.index');
    Route::put('/settings', [StoreOwner\StoreSettingController::class, 'update'])->name('settings.update');

    // Reports
    Route::get('/reports/sales', [StoreOwner\ReportController::class, 'sales'])->name('reports.sales');
    Route::get('/reports/inventory', [StoreOwner\ReportController::class, 'inventory'])->name('reports.inventory');
});

