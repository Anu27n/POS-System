<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin;
use App\Http\Controllers\StoreOwner;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InstallerController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PricingController;
use App\Http\Controllers\StoreController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Installer Routes
|--------------------------------------------------------------------------
*/

Route::prefix('install')->withoutMiddleware(\App\Http\Middleware\CheckInstallation::class)->group(function () {
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

// Debug route for testing sessions
Route::get('/test-session', function() {
    $count = session('test_count', 0);
    session(['test_count' => $count + 1]);
    return response()->json([
        'session_driver' => config('session.driver'),
        'test_count' => session('test_count'),
        'session_id' => session()->getId(),
        'auth_check' => auth()->check(),
        'auth_user' => auth()->user(),
        'cookie_config' => [
            'secure' => config('session.secure'),
            'same_site' => config('session.same_site'),
            'domain' => config('session.domain'),
        ]
    ]);
});

// Pricing page
Route::get('/pricing', [PricingController::class, 'index'])->name('pricing');

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
    
    // Password Reset Routes
    Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPasswordForm'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
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

    // Plan checkout
    Route::get('/pricing/{plan}/checkout', [PricingController::class, 'checkout'])->name('pricing.checkout');
    Route::post('/pricing/{plan}/subscribe', [PricingController::class, 'subscribe'])->name('pricing.subscribe');
    Route::get('/pricing/{plan}/payment', [PricingController::class, 'payment'])->name('pricing.payment');
    Route::post('/pricing/{plan}/razorpay-callback', [PricingController::class, 'razorpayCallback'])->name('pricing.razorpay-callback');
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

    // Customization settings
    Route::get('/settings/customization', [Admin\CustomizationController::class, 'index'])->name('settings.customization');
    Route::post('/settings/customization', [Admin\CustomizationController::class, 'update'])->name('settings.customization.update');
    Route::get('/settings/customization/remove-logo', [Admin\CustomizationController::class, 'removeLogo'])->name('settings.customization.remove-logo');
    Route::get('/settings/customization/remove-favicon', [Admin\CustomizationController::class, 'removeFavicon'])->name('settings.customization.remove-favicon');

    // SMTP & Email settings
    Route::get('/settings/smtp', [Admin\SmtpSettingController::class, 'index'])->name('settings.smtp');
    Route::post('/settings/smtp', [Admin\SmtpSettingController::class, 'update'])->name('settings.smtp.update');
    Route::post('/settings/smtp/test', [Admin\SmtpSettingController::class, 'test'])->name('settings.smtp.test');

    // Reports
    Route::get('/reports/sales', [Admin\ReportController::class, 'sales'])->name('reports.sales');
    Route::get('/reports/orders', [Admin\ReportController::class, 'orders'])->name('reports.orders');

    // Plan management
    Route::resource('plans', Admin\PlanController::class);
    Route::post('/plans/{plan}/toggle-status', [Admin\PlanController::class, 'toggleStatus'])->name('plans.toggle-status');

    // Plan features management
    Route::resource('plan-features', Admin\PlanFeatureController::class)->except(['show']);
    Route::post('/plan-features/seed-defaults', [Admin\PlanFeatureController::class, 'seedDefaults'])->name('plan-features.seed-defaults');

    // Order management with QR Scanner
    Route::get('/orders', [Admin\OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/scanner', [Admin\OrderController::class, 'scanner'])->name('orders.scanner');
    Route::get('/orders/{order}', [Admin\OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/scan', [Admin\OrderController::class, 'scan'])->name('orders.scan');
    Route::post('/orders/{order}/update-status', [Admin\OrderController::class, 'updateStatus'])->name('orders.update-status');
    Route::post('/orders/{order}/mark-paid', [Admin\OrderController::class, 'markPaid'])->name('orders.mark-paid');
    Route::post('/orders/{order}/complete', [Admin\OrderController::class, 'completeOrder'])->name('orders.complete');
    Route::get('/orders/{order}/receipt', [Admin\OrderController::class, 'receipt'])->name('orders.receipt');
});

/*
|--------------------------------------------------------------------------
| Store Owner Routes
|--------------------------------------------------------------------------
*/

Route::prefix('store-owner')->name('store-owner.')->middleware(['auth', 'role:store_owner,staff'])->group(function () {
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
    Route::post('/pos/{order}/complete', [StoreOwner\POSController::class, 'completeOrder'])->name('pos.complete-order');

    // Customer management
    Route::resource('customers', StoreOwner\CustomerController::class);

    // Staff management
    Route::resource('staff', StoreOwner\StaffController::class);
    Route::post('/staff/{staff}/toggle-status', [StoreOwner\StaffController::class, 'toggleStatus'])->name('staff.toggle-status');

    // Store settings
    Route::get('/settings', [StoreOwner\StoreSettingController::class, 'index'])->name('settings.index');
    Route::put('/settings', [StoreOwner\StoreSettingController::class, 'update'])->name('settings.update');

    // Store customization (plan-based feature)
    Route::get('/customization', [StoreOwner\CustomizationController::class, 'index'])->name('customization.index');
    Route::put('/customization', [StoreOwner\CustomizationController::class, 'update'])->name('customization.update');
    Route::get('/customization/remove-logo', [StoreOwner\CustomizationController::class, 'removeLogo'])->name('customization.remove-logo');
    Route::get('/customization/reset-colors', [StoreOwner\CustomizationController::class, 'resetColors'])->name('customization.reset-colors');

    // Payment settings
    Route::get('/payment-settings', [StoreOwner\PaymentSettingsController::class, 'index'])->name('payment-settings.index');
    Route::put('/payment-settings', [StoreOwner\PaymentSettingsController::class, 'update'])->name('payment-settings.update');

    // QR Code management
    Route::get('/qr-code', [StoreOwner\QRCodeController::class, 'index'])->name('qr-code.index');
    Route::post('/qr-code/generate', [StoreOwner\QRCodeController::class, 'generate'])->name('qr-code.generate');
    Route::get('/qr-code/download', [StoreOwner\QRCodeController::class, 'download'])->name('qr-code.download');

    // Reports
    Route::get('/reports/sales', [StoreOwner\ReportController::class, 'sales'])->name('reports.sales');
    Route::get('/reports/inventory', [StoreOwner\ReportController::class, 'inventory'])->name('reports.inventory');
    Route::get('/reports/tax', [StoreOwner\TaxReportController::class, 'index'])->name('reports.tax');
    Route::get('/reports/tax/export', [StoreOwner\TaxReportController::class, 'export'])->name('reports.tax.export');

    // Tax settings
    Route::get('/tax-settings', [StoreOwner\TaxSettingController::class, 'index'])->name('tax-settings.index');
    Route::put('/tax-settings', [StoreOwner\TaxSettingController::class, 'updateSettings'])->name('tax-settings.update');
    Route::post('/tax-settings/tax', [StoreOwner\TaxSettingController::class, 'storeTax'])->name('tax-settings.store-tax');
    Route::put('/tax-settings/tax/{tax}', [StoreOwner\TaxSettingController::class, 'updateTax'])->name('tax-settings.update-tax');
    Route::delete('/tax-settings/tax/{tax}', [StoreOwner\TaxSettingController::class, 'destroyTax'])->name('tax-settings.destroy-tax');
    Route::post('/tax-settings/tax/{tax}/toggle', [StoreOwner\TaxSettingController::class, 'toggleTax'])->name('tax-settings.toggle-tax');

    // Cash Register
    Route::get('/cash-register', [StoreOwner\CashRegisterController::class, 'index'])->name('cash-register.index');
    Route::post('/cash-register/open', [StoreOwner\CashRegisterController::class, 'open'])->name('cash-register.open');
    Route::post('/cash-register/{session}/close', [StoreOwner\CashRegisterController::class, 'close'])->name('cash-register.close');
    Route::post('/cash-register/{session}/add-cash', [StoreOwner\CashRegisterController::class, 'addCash'])->name('cash-register.add-cash');
    Route::get('/cash-register/check-session', [StoreOwner\CashRegisterController::class, 'checkSession'])->name('cash-register.check-session');
    Route::get('/cash-register/reports', [StoreOwner\CashRegisterController::class, 'reports'])->name('cash-register.reports');
    Route::get('/cash-register/{session}', [StoreOwner\CashRegisterController::class, 'show'])->name('cash-register.show');

    // POS Customer search API
    Route::get('/pos/customers/search', [StoreOwner\POSController::class, 'searchCustomers'])->name('pos.customers.search');
    Route::post('/pos/customers/create', [StoreOwner\POSController::class, 'createCustomer'])->name('pos.customers.create');
    Route::get('/pos/order/lookup', [StoreOwner\POSController::class, 'lookupOrder'])->name('pos.order.lookup');
});
