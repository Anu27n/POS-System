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
    Route::get('/license', [InstallerController::class, 'license'])->name('installer.license');
    Route::post('/license', [InstallerController::class, 'licenseStore'])->name('installer.license.store');
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

// Repair Job Tracking (Public)
Route::get('/track', [App\Http\Controllers\TrackRepairController::class, 'index'])->name('track.index');
Route::post('/track', [App\Http\Controllers\TrackRepairController::class, 'search'])->name('track.search');
Route::get('/track/{ticketNumber}', [App\Http\Controllers\TrackRepairController::class, 'show'])->name('track.show');

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

/*
|--------------------------------------------------------------------------
| Payment Routes (Order Payments)
|--------------------------------------------------------------------------
*/

Route::get('/payment/razorpay/failed', [App\Http\Controllers\PaymentController::class, 'razorpayFailed'])->name('payment.razorpay.failed');
Route::get('/payment/razorpay/{orderNumber}', [App\Http\Controllers\PaymentController::class, 'razorpay'])->name('payment.razorpay');
Route::post('/payment/razorpay/callback', [App\Http\Controllers\PaymentController::class, 'razorpayCallback'])->name('payment.razorpay.callback');
Route::get('/payment/stripe/{orderNumber}', [App\Http\Controllers\PaymentController::class, 'stripe'])->name('payment.stripe');
Route::post('/payment/stripe/callback', [App\Http\Controllers\PaymentController::class, 'stripeCallback'])->name('payment.stripe.callback');

Route::middleware('auth')->group(function () {
    // Customer orders
    Route::get('/my-orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/my-orders/{order}', [OrderController::class, 'show'])->name('orders.show');

    // Plan checkout
    Route::get('/pricing/{plan}/checkout', [PricingController::class, 'checkout'])->name('pricing.checkout');
    Route::post('/pricing/{plan}/subscribe', [PricingController::class, 'subscribe'])->name('pricing.subscribe');
    Route::get('/pricing/{plan}/payment', [PricingController::class, 'payment'])->name('pricing.payment');
    Route::post('/pricing/{plan}/razorpay-callback', [PricingController::class, 'razorpayCallback'])->name('pricing.razorpay-callback');
    Route::post('/pricing/{plan}/stripe-callback', [PricingController::class, 'stripeCallback'])->name('pricing.stripe-callback');
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
    Route::get('/technician-dashboard', [StoreOwner\TechnicianDashboardController::class, 'index'])->name('technician-dashboard');

    // Store creation for new store owners
    Route::get('/stores/create', [StoreOwner\StoreCreateController::class, 'create'])->name('stores.create');
    Route::post('/stores', [StoreOwner\StoreCreateController::class, 'store'])->name('stores.store');

    // Category management
    Route::resource('categories', StoreOwner\CategoryController::class)->except(['show'])->middleware(['permission:manage_categories', 'plan.feature:category_management']);

    // Product management
    Route::resource('products', StoreOwner\ProductController::class)->middleware(['permission:manage_products', 'plan.feature:product_management']);
    Route::post('/products/{product}/update-stock', [StoreOwner\ProductController::class, 'updateStock'])->name('products.update-stock')->middleware(['permission:manage_products', 'plan.feature:stock_tracking']);

    // Order management
    Route::middleware('permission:view_orders')->group(function() {
        Route::get('/orders', [StoreOwner\OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [StoreOwner\OrderController::class, 'show'])->name('orders.show');
        Route::get('/orders/{order}/receipt', [StoreOwner\OrderController::class, 'receipt'])->name('orders.receipt');
    });
    Route::patch('/orders/{order}/status', [StoreOwner\OrderController::class, 'updateStatus'])->name('orders.update-status')->middleware('permission:manage_orders');

    // POS
    Route::middleware(['permission:use_pos', 'plan.feature:pos_terminal'])->group(function() {
        Route::get('/pos', [StoreOwner\POSController::class, 'index'])->name('pos.index');
        Route::post('/pos/process', [StoreOwner\POSController::class, 'process'])->name('pos.process');
        Route::post('/pos/scan', [StoreOwner\POSController::class, 'scan'])->name('pos.scan');
        Route::post('/pos/{order}/mark-paid', [StoreOwner\POSController::class, 'markPaid'])->name('pos.mark-paid');
        Route::post('/pos/{order}/complete', [StoreOwner\POSController::class, 'completeOrder'])->name('pos.complete-order');
    });

    // Customer management
    Route::resource('customers', StoreOwner\CustomerController::class)->middleware(['permission:manage_customers', 'plan.feature:customer_database']);

    // Repair Job Management
    // Static routes first (must come before parameterized routes)
    Route::middleware('permission:manage_repair_jobs')->group(function() {
        Route::get('/repair-jobs/create', [StoreOwner\RepairJobController::class, 'create'])->name('repair-jobs.create');
        Route::post('/repair-jobs', [StoreOwner\RepairJobController::class, 'store'])->name('repair-jobs.store');
    });
    // Index route (no parameter)
    Route::middleware('permission:view_repair_jobs')->group(function() {
        Route::get('/repair-jobs', [StoreOwner\RepairJobController::class, 'index'])->name('repair-jobs.index');
    });
    // Parameterized routes come after static routes
    Route::middleware('permission:view_repair_jobs')->group(function() {
        Route::get('/repair-jobs/{repairJob}', [StoreOwner\RepairJobController::class, 'show'])->name('repair-jobs.show');
        Route::get('/repair-jobs/{repairJob}/print', [StoreOwner\RepairJobController::class, 'printJobCard'])->name('repair-jobs.print');
        Route::get('/repair-jobs/{repairJob}/invoice', [StoreOwner\RepairJobController::class, 'invoice'])->name('repair-jobs.invoice');
    });
    Route::middleware('permission:manage_repair_jobs')->group(function() {
        Route::get('/repair-jobs/{repairJob}/edit', [StoreOwner\RepairJobController::class, 'edit'])->name('repair-jobs.edit');
        Route::put('/repair-jobs/{repairJob}', [StoreOwner\RepairJobController::class, 'update'])->name('repair-jobs.update');
        Route::delete('/repair-jobs/{repairJob}', [StoreOwner\RepairJobController::class, 'destroy'])->name('repair-jobs.destroy');
        Route::patch('/repair-jobs/{repairJob}/assign', [StoreOwner\RepairJobController::class, 'assignTechnician'])->name('repair-jobs.assign');
    });
    Route::middleware('permission:update_job_status')->group(function() {
        Route::patch('/repair-jobs/{repairJob}/status', [StoreOwner\RepairJobController::class, 'updateStatus'])->name('repair-jobs.update-status');
    });
    Route::middleware('permission:add_repair_parts')->group(function() {
        Route::post('/repair-jobs/{repairJob}/parts', [StoreOwner\RepairJobController::class, 'addPart'])->name('repair-jobs.add-part');
        Route::delete('/repair-jobs/{repairJob}/parts/{part}', [StoreOwner\RepairJobController::class, 'removePart'])->name('repair-jobs.remove-part');
    });

    // Staff management
    Route::resource('staff', StoreOwner\StaffController::class)->middleware(['permission:manage_staff', 'plan.feature:staff_accounts']);
    Route::post('/staff/{staff}/toggle-status', [StoreOwner\StaffController::class, 'toggleStatus'])->name('staff.toggle-status')->middleware(['permission:manage_staff', 'plan.feature:staff_accounts']);

    // Store settings
    Route::get('/settings', [StoreOwner\StoreSettingController::class, 'index'])->name('settings.index')->middleware('permission:manage_settings');
    Route::put('/settings', [StoreOwner\StoreSettingController::class, 'update'])->name('settings.update')->middleware('permission:manage_settings');

    // Store customization (plan-based feature)
    Route::get('/customization', [StoreOwner\CustomizationController::class, 'index'])->name('customization.index')->middleware(['permission:manage_settings', 'plan.feature:store_customization']);
    Route::put('/customization', [StoreOwner\CustomizationController::class, 'update'])->name('customization.update')->middleware(['permission:manage_settings', 'plan.feature:store_customization']);
    Route::get('/customization/remove-logo', [StoreOwner\CustomizationController::class, 'removeLogo'])->name('customization.remove-logo')->middleware(['permission:manage_settings', 'plan.feature:store_customization']);
    Route::get('/customization/reset-colors', [StoreOwner\CustomizationController::class, 'resetColors'])->name('customization.reset-colors')->middleware(['permission:manage_settings', 'plan.feature:store_customization']);

    // Payment settings
    Route::get('/payment-settings', [StoreOwner\PaymentSettingsController::class, 'index'])->name('payment-settings.index')->middleware('permission:manage_settings');
    Route::put('/payment-settings', [StoreOwner\PaymentSettingsController::class, 'update'])->name('payment-settings.update')->middleware('permission:manage_settings');

    // QR Code management
    Route::get('/qr-code', [StoreOwner\QRCodeController::class, 'index'])->name('qr-code.index')->middleware('plan.feature:qr_ordering');
    Route::post('/qr-code/generate', [StoreOwner\QRCodeController::class, 'generate'])->name('qr-code.generate')->middleware('plan.feature:qr_ordering');
    Route::get('/qr-code/download', [StoreOwner\QRCodeController::class, 'download'])->name('qr-code.download')->middleware('plan.feature:qr_ordering');

    // Reports
    Route::middleware('permission:view_reports')->group(function() {
        Route::get('/reports/sales', [StoreOwner\ReportController::class, 'sales'])->name('reports.sales')->middleware('plan.feature:sales_reports');
        Route::get('/reports/inventory', [StoreOwner\ReportController::class, 'inventory'])->name('reports.inventory')->middleware('plan.feature:inventory_reports');
        Route::get('/reports/tax', [StoreOwner\TaxReportController::class, 'index'])->name('reports.tax')->middleware('plan.feature:tax_reports');
        Route::get('/reports/tax/export', [StoreOwner\TaxReportController::class, 'export'])->name('reports.tax.export')->middleware('plan.feature:tax_reports');
    });

    // Tax settings
    Route::middleware(['permission:manage_settings', 'plan.feature:tax_management'])->group(function() {
        Route::get('/tax-settings', [StoreOwner\TaxSettingController::class, 'index'])->name('tax-settings.index');
        Route::put('/tax-settings', [StoreOwner\TaxSettingController::class, 'updateSettings'])->name('tax-settings.update');
        Route::post('/tax-settings/tax', [StoreOwner\TaxSettingController::class, 'storeTax'])->name('tax-settings.store-tax');
        Route::put('/tax-settings/tax/{tax}', [StoreOwner\TaxSettingController::class, 'updateTax'])->name('tax-settings.update-tax');
        Route::delete('/tax-settings/tax/{tax}', [StoreOwner\TaxSettingController::class, 'destroyTax'])->name('tax-settings.destroy-tax');
        Route::post('/tax-settings/tax/{tax}/toggle', [StoreOwner\TaxSettingController::class, 'toggleTax'])->name('tax-settings.toggle-tax');
    });

    // Cash Register
    Route::get('/cash-register', [StoreOwner\CashRegisterController::class, 'index'])->name('cash-register.index');
    Route::post('/cash-register/open', [StoreOwner\CashRegisterController::class, 'open'])->name('cash-register.open');
    Route::post('/cash-register/{session}/close', [StoreOwner\CashRegisterController::class, 'close'])->name('cash-register.close');
    Route::post('/cash-register/{session}/add-cash', [StoreOwner\CashRegisterController::class, 'addCash'])->name('cash-register.add-cash');
    Route::get('/cash-register/check-session', [StoreOwner\CashRegisterController::class, 'checkSession'])->name('cash-register.check-session');
    Route::get('/cash-register/reports', [StoreOwner\CashRegisterController::class, 'reports'])->name('cash-register.reports')->middleware(['permission:view_reports', 'plan.feature:cash_register']);
    Route::get('/cash-register/{session}', [StoreOwner\CashRegisterController::class, 'show'])->name('cash-register.show')->middleware('plan.feature:cash_register');

    // POS Customer search API
    Route::get('/pos/customers/search', [StoreOwner\POSController::class, 'searchCustomers'])->name('pos.customers.search');
    Route::post('/pos/customers/create', [StoreOwner\POSController::class, 'createCustomer'])->name('pos.customers.create');
    Route::get('/pos/order/lookup', [StoreOwner\POSController::class, 'lookupOrder'])->name('pos.order.lookup');
});
