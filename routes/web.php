<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\User\DashboardController as UserDashboard;
use App\Http\Controllers\User\OrderController as UserOrder;
use App\Http\Controllers\User\DepositController as UserDeposit;
use App\Http\Controllers\User\TicketController as UserTicket;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\UserController as AdminUser;
use App\Http\Controllers\Admin\ServiceController as AdminService;
use App\Http\Controllers\Admin\CategoryController as AdminCategory;
use App\Http\Controllers\Admin\ProviderController as AdminProvider;
use App\Http\Controllers\Admin\OrderController as AdminOrder;
use App\Http\Controllers\Admin\PaymentController as AdminPayment;
use App\Http\Controllers\Admin\PaymentMethodController as AdminPaymentMethod;
use App\Http\Controllers\Admin\TicketController as AdminTicket;
use App\Http\Controllers\Admin\ReportController as AdminReport;
use App\Http\Controllers\Admin\SettingController as AdminSetting;

// ===== PUBLIC ROUTES =====
Route::get('/', [HomeController::class, 'getIndex'])->name('home');

// Temporary route to handle Hostinger setup easily (Migrate DB)
Route::get('/hostinger-setup', function () {
    try {
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        \Illuminate\Support\Facades\Artisan::call('cache:clear');
        \Illuminate\Support\Facades\Artisan::call('view:clear');
        \Illuminate\Support\Facades\Artisan::call('route:clear');
        return "Hostinger Database Migrated Successfully! Your tables are ready. You can now access your website.";
    } catch (\Exception $e) {
        return "Error migrating: " . $e->getMessage();
    }
});
Route::get('/services', [HomeController::class, 'getServices'])->name('services');
Route::get('/services/{id}', [HomeController::class, 'getServiceDetails'])->name('service.details');
Route::get('/pricing', [HomeController::class, 'getPricing'])->name('pricing');
Route::get('/about', [HomeController::class, 'getAbout'])->name('about');
Route::get('/terms', [HomeController::class, 'getTerms'])->name('terms');
Route::get('/privacy', [HomeController::class, 'getPrivacy'])->name('privacy');
Route::post('/contact', [HomeController::class, 'postContact'])->name('contact');

// ===== AUTH ROUTES =====
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
    Route::get('/forgot-password', [ForgotPasswordController::class, 'getEmail'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'postEmail'])->name('password.email');
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'getReset'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'postReset'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});

// ===== USER DASHBOARD ROUTES =====
Route::middleware('auth')->prefix('user')->name('user.')->group(function () {
    Route::get('/dashboard', [UserDashboard::class, 'getIndex'])->name('dashboard');
    Route::get('/profile', [UserDashboard::class, 'getProfile'])->name('profile');
    Route::post('/profile', [UserDashboard::class, 'postProfile']);
    Route::post('/change-password', [UserDashboard::class, 'postChangePassword'])->name('change-password');
    Route::get('/api-key', [UserDashboard::class, 'getApiKey'])->name('api-key');
    Route::post('/api-key/regenerate', [UserDashboard::class, 'postRegenerateApiKey'])->name('api-key.regenerate');

    // Orders
    Route::get('/orders', [UserOrder::class, 'getIndex'])->name('orders');
    Route::get('/orders/new', [UserOrder::class, 'getNew'])->name('orders.new');
    Route::post('/orders', [UserOrder::class, 'postStore'])->name('orders.store');
    Route::post('/orders/bulk', [UserOrder::class, 'postBulk'])->name('orders.bulk');
    Route::post('/orders/dripfeed', [UserOrder::class, 'postDripfeed'])->name('orders.dripfeed');
    Route::post('/orders/subscription', [UserOrder::class, 'postSubscription'])->name('orders.subscription');
    Route::get('/orders/{orderId}', [UserOrder::class, 'getView'])->name('orders.view');
    Route::post('/orders/{orderId}/cancel', [UserOrder::class, 'postCancel'])->name('orders.cancel');
    Route::post('/orders/{orderId}/refill', [UserOrder::class, 'postRefill'])->name('orders.refill');
    Route::get('/orders/export/csv', [UserOrder::class, 'getExport'])->name('orders.export');

    // AJAX: Get service price
    Route::get('/services/price/{id}', [UserOrder::class, 'getServicePrice'])->name('services.price');
    Route::get('/services/by-category/{categoryId}', [UserOrder::class, 'getServicesByCategory'])->name('services.by-category');

    // Deposits
    Route::get('/deposit', [UserDeposit::class, 'getIndex'])->name('deposit');
    Route::get('/deposit/method/{id}', [UserDeposit::class, 'getMethod'])->name('deposit.method');
    Route::post('/deposit/select', [UserDeposit::class, 'postSelect'])->name('deposit.select');
    Route::get('/deposit/submit-txn/{transactionId}', [UserDeposit::class, 'getSubmitTxn'])->name('deposit.submit-txn');
    Route::post('/deposit/submit-txn/{transactionId}', [UserDeposit::class, 'postSubmitTxn']);
    Route::get('/deposit/stripe/{transactionId}', [UserDeposit::class, 'getStripe'])->name('deposit.stripe');
    Route::get('/deposit/paypal/{transactionId}', [UserDeposit::class, 'getPaypal'])->name('deposit.paypal');
    Route::get('/deposit/crypto/{transactionId}', [UserDeposit::class, 'getCrypto'])->name('deposit.crypto');
    Route::get('/deposit/success/{transactionId}', [UserDeposit::class, 'getSuccess'])->name('deposit.success');
    Route::get('/deposit/cancel/{transactionId}', [UserDeposit::class, 'getCancel'])->name('deposit.cancel');

    // Tickets
    Route::get('/tickets', [UserTicket::class, 'getIndex'])->name('tickets');
    Route::get('/tickets/create', [UserTicket::class, 'getCreate'])->name('tickets.create');
    Route::post('/tickets', [UserTicket::class, 'postStore'])->name('tickets.store');
    Route::get('/tickets/{id}', [UserTicket::class, 'getView'])->name('tickets.view');
    Route::post('/tickets/{id}/reply', [UserTicket::class, 'postReply'])->name('tickets.reply');
    Route::post('/tickets/{id}/close', [UserTicket::class, 'postClose'])->name('tickets.close');
});

// ===== ADMIN ROUTES =====
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboard::class, 'getIndex'])->name('dashboard');

    // Users
    Route::get('/users', [AdminUser::class, 'getIndex'])->name('users');
    Route::get('/users/create', [AdminUser::class, 'getCreate'])->name('users.create');
    Route::post('/users', [AdminUser::class, 'postStore'])->name('users.store');
    Route::get('/users/{id}/edit', [AdminUser::class, 'getEdit'])->name('users.edit');
    Route::post('/users/{id}', [AdminUser::class, 'postUpdate'])->name('users.update');
    Route::post('/users/{id}/suspend', [AdminUser::class, 'postSuspend'])->name('users.suspend');
    Route::post('/users/{id}/delete', [AdminUser::class, 'postDelete'])->name('users.delete');
    Route::get('/users/export/csv', [AdminUser::class, 'getExport'])->name('users.export');

    // Categories
    Route::get('/categories', [AdminCategory::class, 'getIndex'])->name('categories');
    Route::post('/categories', [AdminCategory::class, 'postStore'])->name('categories.store');
    Route::post('/categories/{id}', [AdminCategory::class, 'postUpdate'])->name('categories.update');
    Route::post('/categories/{id}/delete', [AdminCategory::class, 'postDelete'])->name('categories.delete');

    // Services
    Route::get('/services', [AdminService::class, 'getIndex'])->name('services');
    Route::get('/services/create', [AdminService::class, 'getCreate'])->name('services.create');
    Route::post('/services', [AdminService::class, 'postStore'])->name('services.store');
    Route::get('/services/{id}/edit', [AdminService::class, 'getEdit'])->name('services.edit');
    Route::post('/services/{id}', [AdminService::class, 'postUpdate'])->name('services.update');
    Route::post('/services/{id}/delete', [AdminService::class, 'postDelete'])->name('services.delete');
    Route::post('/services/bulk-action', [AdminService::class, 'postBulkAction'])->name('services.bulk-action');

    // Providers
    Route::get('/providers', [AdminProvider::class, 'getIndex'])->name('providers');
    Route::get('/providers/create', [AdminProvider::class, 'getCreate'])->name('providers.create');
    Route::post('/providers', [AdminProvider::class, 'postStore'])->name('providers.store');
    Route::post('/providers/{id}/delete', [AdminProvider::class, 'postDelete'])->name('providers.delete');
    Route::post('/providers/{id}/sync', [AdminProvider::class, 'postSync'])->name('providers.sync');
    Route::post('/providers/{id}/sync-services', [AdminProvider::class, 'postSyncServices'])->name('providers.sync-services');
    Route::post('/providers/{id}/balance', [AdminProvider::class, 'postCheckBalance'])->name('providers.balance');

    // Orders
    Route::get('/orders', [AdminOrder::class, 'getIndex'])->name('orders');
    Route::get('/orders/{id}', [AdminOrder::class, 'getView'])->name('orders.view');
    Route::post('/orders/{id}/cancel', [AdminOrder::class, 'postCancel'])->name('orders.cancel');
    Route::post('/orders/{id}/refund', [AdminOrder::class, 'postRefund'])->name('orders.refund');
    Route::post('/orders/{id}/partial', [AdminOrder::class, 'postPartial'])->name('orders.partial');
    Route::get('/orders/export/csv', [AdminOrder::class, 'getExport'])->name('orders.export');

    // Payments (Deposits)
    Route::get('/payments', [AdminPayment::class, 'getIndex'])->name('payments');
    Route::get('/payments/{id}', [AdminPayment::class, 'getView'])->name('payments.view');
    Route::post('/payments/{id}/approve', [AdminPayment::class, 'postApprove'])->name('payments.approve');
    Route::post('/payments/{id}/reject', [AdminPayment::class, 'postReject'])->name('payments.reject');
    Route::post('/payments/{id}/refund', [AdminPayment::class, 'postRefund'])->name('payments.refund');
    Route::get('/payments/export/csv', [AdminPayment::class, 'getExport'])->name('payments.export');

    // Payment Methods
    Route::get('/payment-methods', [AdminPaymentMethod::class, 'getIndex'])->name('payment-methods');
    Route::get('/payment-methods/create', [AdminPaymentMethod::class, 'getCreate'])->name('payment-methods.create');
    Route::post('/payment-methods', [AdminPaymentMethod::class, 'postStore'])->name('payment-methods.store');
    Route::get('/payment-methods/{id}/edit', [AdminPaymentMethod::class, 'getEdit'])->name('payment-methods.edit');
    Route::post('/payment-methods/{id}', [AdminPaymentMethod::class, 'postUpdate'])->name('payment-methods.update');
    Route::post('/payment-methods/{id}/delete', [AdminPaymentMethod::class, 'postDelete'])->name('payment-methods.delete');
    Route::post('/payment-methods/{id}/toggle', [AdminPaymentMethod::class, 'postToggle'])->name('payment-methods.toggle');

    // Tickets
    Route::get('/tickets', [AdminTicket::class, 'getIndex'])->name('tickets');
    Route::get('/tickets/{id}', [AdminTicket::class, 'getView'])->name('tickets.view');
    Route::post('/tickets/{id}/reply', [AdminTicket::class, 'postReply'])->name('tickets.reply');
    Route::post('/tickets/{id}/close', [AdminTicket::class, 'postClose'])->name('tickets.close');
    Route::post('/tickets/{id}/priority', [AdminTicket::class, 'postChangePriority'])->name('tickets.priority');

    // Reports
    Route::get('/reports/profit', [AdminReport::class, 'getProfit'])->name('reports.profit');
    Route::get('/reports/orders', [AdminReport::class, 'getOrders'])->name('reports.orders');
    Route::get('/reports/payments', [AdminReport::class, 'getPayments'])->name('reports.payments');
    Route::get('/reports/activity', [AdminReport::class, 'getActivity'])->name('reports.activity');

    // Settings
    Route::get('/settings', [AdminSetting::class, 'getIndex'])->name('settings');
    Route::post('/settings', [AdminSetting::class, 'postSave'])->name('settings.save');
    Route::get('/settings/seo', [AdminSetting::class, 'getSeo'])->name('settings.seo');
    Route::post('/settings/seo', [AdminSetting::class, 'postSeo'])->name('settings.seo.save');
    Route::get('/settings/theme', [AdminSetting::class, 'getTheme'])->name('settings.theme');
    Route::post('/settings/theme', [AdminSetting::class, 'postTheme'])->name('settings.theme.save');
});

// ===== API ROUTES =====
Route::prefix('api/v1')->name('api.v1.')->group(function () {
    // Public
    Route::get('/services', [App\Http\Controllers\Api\UserApiController::class, 'getServices'])->name('services');
    Route::get('/categories', [App\Http\Controllers\Api\UserApiController::class, 'getCategories'])->name('categories');
    Route::get('/balance', [App\Http\Controllers\Api\UserApiController::class, 'getBalance'])->name('balance');

    // Authenticated (API Key)
    Route::middleware('api.key')->group(function () {
        Route::post('/order', [App\Http\Controllers\Api\UserApiController::class, 'postOrder'])->name('order');
        Route::get('/order/{orderId}', [App\Http\Controllers\Api\UserApiController::class, 'getOrder'])->name('order.status');
        Route::get('/orders', [App\Http\Controllers\Api\UserApiController::class, 'getOrders'])->name('orders');
        Route::post('/refill', [App\Http\Controllers\Api\UserApiController::class, 'postRefill'])->name('refill');
        Route::get('/refill/{refillId}', [App\Http\Controllers\Api\UserApiController::class, 'getRefillStatus'])->name('refill.status');
        Route::get('/profile', [App\Http\Controllers\Api\UserApiController::class, 'getProfile'])->name('profile');
        Route::post('/calculate', [App\Http\Controllers\Api\UserApiController::class, 'postCalculate'])->name('calculate');
    });
});
