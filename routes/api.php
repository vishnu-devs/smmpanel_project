<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AppApiController;

/*
|--------------------------------------------------------------------------
| Mobile App API Routes (v1)
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    // 1. Authentication (Public)
    Route::post('auth/login', [AppApiController::class, 'login'])->middleware('throttle:mobile-login');
    Route::post('auth/register', [AppApiController::class, 'register'])->middleware('throttle:mobile-register');
    Route::post('auth/google', [AppApiController::class, 'googleAuth'])->middleware('throttle:mobile-google');

    // 2. App Config & Catalog
    Route::get('app-config', [AppApiController::class, 'getAppConfig']);
    Route::get('catalog', [AppApiController::class, 'getCatalog'])->middleware('throttle:catalog');
    Route::get('services', [AppApiController::class, 'getCatalog'])->middleware('throttle:catalog');

    // 3. User Profile & Balance
    Route::get('user/profile', [AppApiController::class, 'getProfile']);
    Route::post('user/update-profile', [AppApiController::class, 'updateProfile']);
    Route::post('user/change-password', [AppApiController::class, 'changePassword']);
    Route::post('user/api-key', [AppApiController::class, 'generateApiKey']);

    // 4. Orders
    Route::post('orders/create', [AppApiController::class, 'placeOrder'])->middleware('throttle:mobile-orders');
    Route::post('orders/{id}/cancel', [AppApiController::class, 'cancelOrder'])->middleware('throttle:mobile-orders');
    Route::post('orders/{id}/refill', [AppApiController::class, 'refillOrder'])->middleware('throttle:mobile-orders');
    Route::get('orders', [AppApiController::class, 'getOrders']);

    // 5. Payments & Wallet History
    Route::get('payments/qr-details', [AppApiController::class, 'getPaymentDetails']);
    Route::post('payments/submit-utr', [AppApiController::class, 'submitUtr'])->middleware('throttle:mobile-utr');
    Route::get('payments/history', [AppApiController::class, 'getPaymentHistory']);

    // 6. Daily Scratch Card
    Route::get('scratch-card/today', [AppApiController::class, 'getScratchCardToday']);
    Route::post('scratch-card/scratch', [AppApiController::class, 'scratchCard']);

    // 7. Referrals & Earn
    Route::get('referrals', [AppApiController::class, 'getReferrals']);

    // 8. Tickets & Support
    Route::get('tickets', [AppApiController::class, 'getTickets']);
    Route::post('tickets/create', [AppApiController::class, 'createTicket'])->middleware('throttle:mobile-tickets');
    Route::post('tickets/{id}/reply', [AppApiController::class, 'replyTicket'])->middleware('throttle:mobile-tickets');

    // 9. Customer Feedback
    Route::get('feedback', [AppApiController::class, 'getFeedback']);
    Route::post('feedback/create', [AppApiController::class, 'createFeedback']);

    // 10. Blogs & Policy Info Pages
    Route::get('blogs', [AppApiController::class, 'getBlogs']);
    Route::get('blogs/{slug}', [AppApiController::class, 'getBlogDetail']);
    Route::get('info/{page}', [AppApiController::class, 'getInfoPage']);
});