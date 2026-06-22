<?php

use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/products/{id}', [ProductController::class, 'show'])->name('api.products.show');

// Cart Sync - sync localStorage cart to session
Route::post('/cart/sync', function (\Illuminate\Http\Request $request) {
    $cart = $request->input('cart', []);
    session(['abodafarm.cart' => $cart]);
    return response()->json(['success' => true, 'cart_count' => count($cart)]);
})->name('api.cart.sync');

// Midtrans Payment Routes
Route::prefix('payments')->group(function () {
    // Generate Snap Token for payment
    Route::post('/snap-token', [PaymentController::class, 'snapToken'])->name('api.payments.snap-token');

    // Handle Midtrans notification/webhook
    Route::post('/notification', [PaymentController::class, 'notification'])->name('api.payments.notification');

    // Get payment status
    Route::get('/status', [PaymentController::class, 'status'])->name('api.payments.status');
});
