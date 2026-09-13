<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\ResellerApiController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\ScratchCardController;

// Public Front-facing Routes
Route::get('/', [LandingController::class, 'index'])->name('home');
Route::get('/services', [LandingController::class, 'services'])->name('services');
Route::get('/api-docs', [LandingController::class, 'apiDocs'])->name('api.docs');
Route::get('/faq', [LandingController::class, 'faq'])->name('faq');
Route::get('/rules', [LandingController::class, 'rules'])->name('rules');
Route::get('/resellers', [LandingController::class, 'resellers'])->name('resellers');
Route::get('/providers', [LandingController::class, 'resellers'])->name('providers');
Route::get('/privacy-policy', [LandingController::class, 'privacyPolicy'])->name('privacy.policy');
Route::redirect('/privacy', '/privacy-policy', 301);
Route::get('/download-app', [LandingController::class, 'downloadApp'])->name('app.download');
Route::get('/manifest.json', [LandingController::class, 'manifest'])->name('manifest.json');
Route::get('/site.webmanifest', [LandingController::class, 'manifest'])->name('site.webmanifest');
Route::get('/manifest.webmanifest', [LandingController::class, 'manifest'])->name('manifest.webmanifest');
Route::get('/sw.js', [LandingController::class, 'serviceWorker'])->name('sw.js');
Route::get('/images/icons/{file}', [LandingController::class, 'iconFile'])->where('file', '.*')->name('icon.file');

// System License Activation Routes
Route::get('/license', [\App\Http\Controllers\LicenseController::class, 'showActivationForm'])->name('license.index');
Route::post('/license/verify', [\App\Http\Controllers\LicenseController::class, 'verifyKey'])->name('license.verify');

// Public Web Cron Endpoint (for hosting providers without CLI php or external cron services)
Route::match(['get', 'post'], '/cron/run', [AdminController::class, 'runCronWeb'])->name('cron.web.run');
Route::match(['get', 'post'], '/cron', [AdminController::class, 'runCronWeb']);

// Public Blog Routes
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');

// Authentication Routes (Guest)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:register');
    
    // Forgot Password & Reset Routes
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLinkEmail'])->name('password.email')->middleware('throttle:6,1');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update')->middleware('throttle:6,1');

    // 2FA Verification Routes
    Route::get('/login/verify', [AuthController::class, 'showVerifyOtp'])->name('login.verify.otp');
    Route::post('/login/verify', [AuthController::class, 'verifyOtp'])->name('login.verify.otp.submit')->middleware('throttle:login');
    Route::post('/login/verify/resend', [AuthController::class, 'resendOtp'])->name('login.verify.otp.resend')->middleware('throttle:login');
});

// Balance Reminder Unsubscribe Route (Public Signed Route)
Route::get('/balance-reminder/unsubscribe/{user}', [LandingController::class, 'unsubscribeBalanceReminder'])->name('balance.unsubscribe');

// Google Authentication Routes
Route::get('/auth/google', [AuthController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');
Route::get('/api/referral/check-code', [AuthController::class, 'checkReferralCode'])->name('api.referral.check_code');

Route::any('/logout', [AuthController::class, 'logout'])->name('logout');

// Webhook Route for UPI Auto Payment (exempt from CSRF in bootstrap/app.php)
Route::post('/payment/webhook/upi', [PaymentController::class, 'webhook'])->name('payment.webhook.upi');
Route::any('/api/payment/pending-utrs', [PaymentController::class, 'pendingUtrs'])->name('payment.pending_utrs');

// User Dashboard Routes (Authenticated)
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [OrderController::class, 'index'])->name('dashboard');
    Route::get('/user/dashboard/services', [OrderController::class, 'getServicesApi'])->name('dashboard.services.api');
    Route::post('/order/place', [OrderController::class, 'place'])->name('order.place')->middleware('throttle:order');
    Route::post('/order/mass-place', [OrderController::class, 'massPlace'])->name('order.mass-place')->middleware('throttle:order');
    Route::get('/orders', [OrderController::class, 'history'])->name('orders.history');
    Route::post('/orders/{order}/refill', [OrderController::class, 'refill'])->name('orders.refill')->middleware('throttle:order');
    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel')->middleware('throttle:order');
    Route::get('/api/orders/active-status', [OrderController::class, 'activeStatus'])->name('api.orders.active-status');
    
    Route::get('/add-funds', [PaymentController::class, 'addFunds'])->name('add_funds');
    Route::post('/add-funds/pay', [PaymentController::class, 'pay'])->name('add_funds.pay')->middleware('throttle:order');
    Route::post('/add-funds/manual', [PaymentController::class, 'manualPay'])->name('add_funds.manual')->middleware('throttle:order');
    
    Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');
    Route::post('/tickets', [TicketController::class, 'store'])->name('tickets.store')->middleware('throttle:ticket');
    Route::get('/tickets/{ticket}', [TicketController::class, 'show'])->name('tickets.show');
    Route::post('/tickets/{ticket}/reply', [TicketController::class, 'reply'])->name('tickets.reply')->middleware('throttle:ticket');
    
    // Referrals & Payment History
    Route::get('/referrals', [LandingController::class, 'referrals'])->name('referrals');
    Route::get('/payment-history', [PaymentController::class, 'history'])->name('payment.history');

    Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
    Route::post('/profile/update', [AuthController::class, 'profileUpdate'])->name('profile.update');
    Route::post('/profile/api-key', [AuthController::class, 'generateApiKey'])->name('profile.api_key');

    // Daily Scratch Card Routes
    Route::get('/api/scratch-card/today', [ScratchCardController::class, 'getTodayCard'])->name('api.scratch_card.today');
    Route::post('/api/scratch-card/scratch', [ScratchCardController::class, 'scratch'])->name('api.scratch_card.scratch')->middleware('throttle:10,1');

    // Customer Feedback & Feature Suggestions
    Route::get('/feedback', [\App\Http\Controllers\FeedbackController::class, 'index'])->name('feedback.index');
    Route::post('/feedback', [\App\Http\Controllers\FeedbackController::class, 'store'])->name('feedback.store')->middleware('throttle:5,60');
});

// Admin Dashboard Routes (Authenticated + Admin Role)
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('dashboard');
    
    // User Management
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::post('/users/{user}/edit', [AdminController::class, 'userUpdate'])->name('users.update');
    Route::post('/users/{user}/delete', [AdminController::class, 'userDelete'])->name('users.delete');

    // Referral Management
    Route::get('/referrals', [AdminController::class, 'referrals'])->name('referrals');
    Route::post('/referrals/settings', [AdminController::class, 'referralSettingsUpdate'])->name('referrals.settings');
    Route::post('/referrals/{referral}/update-status', [AdminController::class, 'referralUpdateStatus'])->name('referrals.update_status');
    Route::post('/referral-commissions/{commission}/update-status', [AdminController::class, 'referralCommissionUpdateStatus'])->name('referrals.commission_update_status');

    // Customer Tiers Management
    Route::get('/tiers', [AdminController::class, 'tiers'])->name('tiers');
    Route::post('/tiers/store', [AdminController::class, 'tierStore'])->name('tiers.store');
    Route::post('/tiers/{tier}/delete', [AdminController::class, 'tierDelete'])->name('tiers.delete');

    // Individual Customer Discounts Management
    Route::get('/discounts', [AdminController::class, 'discounts'])->name('discounts');
    Route::post('/discounts/store', [AdminController::class, 'discountStore'])->name('discounts.store');
    Route::post('/discounts/{discount}/toggle', [AdminController::class, 'discountToggle'])->name('discounts.toggle');
    Route::post('/discounts/{discount}/delete', [AdminController::class, 'discountDelete'])->name('discounts.delete');
    
    // Category Management
    Route::get('/categories', [AdminController::class, 'categories'])->name('categories');
    Route::post('/categories/store', [AdminController::class, 'categoryStore'])->name('categories.store');
    Route::post('/categories/clean-empty', [AdminController::class, 'categoryCleanEmpty'])->name('categories.clean_empty');
    Route::post('/categories/{category}/toggle-pin', [AdminController::class, 'categoryTogglePin'])->name('categories.toggle_pin');
    Route::post('/categories/{category}/delete', [AdminController::class, 'categoryDelete'])->name('categories.delete');
    
    // Service Management
    Route::get('/services', [AdminController::class, 'services'])->name('services');
    Route::post('/services/store', [AdminController::class, 'serviceStore'])->name('services.store');
    Route::post('/services/{service}/delete', [AdminController::class, 'serviceDelete'])->name('services.delete');
    Route::post('/services/import', [AdminController::class, 'serviceImport'])->name('services.import');
    Route::post('/services/sync-platform-statuses', [AdminController::class, 'syncPlatformStatuses'])->name('services.sync_platform_statuses');
    
    // Provider API Management
    Route::get('/providers', [AdminController::class, 'providers'])->name('providers');
    Route::post('/providers/store', [AdminController::class, 'providerStore'])->name('providers.store');
    Route::post('/providers/{provider}/delete', [AdminController::class, 'providerDelete'])->name('providers.delete');
    Route::post('/providers/{provider}/balance', [AdminController::class, 'providerBalanceRefresh'])->name('providers.balance');
    Route::get('/providers/{provider}/services', [AdminController::class, 'providerServicesList'])->name('providers.services');
    Route::post('/providers/{provider}/sync-services', [AdminController::class, 'providerSyncServices'])->name('providers.sync_services');
    Route::post('/providers/{provider}/import-selected-services', [AdminController::class, 'providerImportSelectedServices'])->name('providers.import_selected_services');
    
    // Order Management
    Route::get('/orders', [AdminController::class, 'orders'])->name('orders');
    Route::post('/orders/{order}/update', [AdminController::class, 'orderUpdate'])->name('orders.update');
    Route::post('/orders/{order}/retry', [AdminController::class, 'orderRetry'])->name('orders.retry');
    Route::get('/orders/{order}/logs', [AdminController::class, 'orderLogs'])->name('orders.logs');
    
    // Support Ticket Management
    Route::get('/tickets', [AdminController::class, 'tickets'])->name('tickets');
    Route::get('/tickets/{ticket}', [AdminController::class, 'ticketShow'])->name('tickets.show');
    Route::post('/tickets/{ticket}/reply', [AdminController::class, 'ticketReply'])->name('tickets.reply');
    
    // Settings Management
    Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
    Route::post('/settings/update', [AdminController::class, 'settingsUpdate'])->name('settings.update');

    // System Health & Monitoring & Cron Jobs
    Route::get('/health', [AdminController::class, 'health'])->name('health');
    Route::post('/cache/clear', [AdminController::class, 'cacheClear'])->name('cache.clear');
    Route::post('/cron/run-manual', [AdminController::class, 'cronRunManual'])->name('cron.run_manual');
    Route::post('/cron/regenerate-key', [AdminController::class, 'cronRegenerateKey'])->name('cron.regenerate_key');
    
    // Payments & Deposit Transactions Management
    Route::get('/transactions', [AdminController::class, 'transactions'])->name('transactions');
    Route::post('/transactions/{id}/approve', [AdminController::class, 'transactionApprove'])->name('transactions.approve');
    Route::post('/transactions/{id}/reject', [AdminController::class, 'transactionReject'])->name('transactions.reject');

    // Secure screenshots download
    Route::get('/transactions/{id}/screenshot', [AdminController::class, 'viewScreenshot'])->name('transactions.screenshot');

    // Blog Articles Management
    Route::get('/blogs', [AdminController::class, 'blogs'])->name('blogs');
    Route::get('/blogs/create', [AdminController::class, 'blogCreate'])->name('blogs.create');
    Route::post('/blogs', [AdminController::class, 'blogStore'])->name('blogs.store');
    Route::get('/blogs/{id}/edit', [AdminController::class, 'blogEdit'])->name('blogs.edit');
    Route::put('/blogs/{id}', [AdminController::class, 'blogUpdate'])->name('blogs.update');
    Route::delete('/blogs/{id}', [AdminController::class, 'blogDelete'])->name('blogs.delete');

    // Customer Feedbacks Management
    Route::get('/feedbacks', [AdminController::class, 'feedbacks'])->name('feedbacks');
    Route::post('/feedbacks/{id}/status', [AdminController::class, 'feedbackStatusUpdate'])->name('feedbacks.status');

    // Dynamic Social Platforms Management
    Route::get('/platforms', [AdminController::class, 'platforms'])->name('platforms');
    Route::post('/platforms/store', [AdminController::class, 'platformStore'])->name('platforms.store');
    Route::post('/platforms/{id}/toggle', [AdminController::class, 'platformToggle'])->name('platforms.toggle');
    Route::post('/platforms/{id}/set-default', [AdminController::class, 'platformSetDefault'])->name('platforms.set_default');
    Route::post('/platforms/{id}/delete', [AdminController::class, 'platformDelete'])->name('platforms.delete');
});

// API Reseller Endpoint (Single POST/ANY route with action dispatcher, CSRF validation excluded in bootstrap/app.php)
Route::any('/api/v1', [ResellerApiController::class, 'handle'])->name('api.reseller')->middleware('throttle:reseller-api');
Route::any('/api/v2', [ResellerApiController::class, 'handle'])->middleware('throttle:reseller-api');

// n8n / Automation Blog Creation API Endpoint
Route::post('/api/v1/blog/create', [\App\Http\Controllers\ApiBlogController::class, 'store'])->name('api.blog.create');
