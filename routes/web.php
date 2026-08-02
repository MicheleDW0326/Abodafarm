<?php

use App\Http\Controllers\Auth\CustomAuthenticatedSessionController;
use App\Livewire\Admin\CustomerManagement;
use App\Livewire\Admin\Dashboard as AdminDashboard;
use App\Livewire\Admin\FinancialReport;
use App\Livewire\Admin\Notifications as AdminNotifications;
use App\Livewire\Admin\OrderManagement;
use App\Livewire\Admin\ProductManagement;
use App\Livewire\Admin\ImportProducts;
use App\Livewire\Admin\StaffManagement;
use App\Livewire\Admin\StoreSetting;
use App\Livewire\Admin\AccountSettings as AdminAccountSettings;
use App\Livewire\Checkout;
use App\Livewire\Home;
use App\Livewire\About;
use App\Livewire\Contact;
use App\Livewire\Gallery;
use App\Livewire\Shop;
use App\Livewire\Shop\ProductDetail;
use App\Livewire\Peternak\Dashboard as PeternakDashboard;
use App\Livewire\Peternak\LivestockManagement;
use App\Livewire\Peternak\Notifications;
use App\Livewire\Peternak\Orders;
use App\Livewire\Peternak\Shipments;
use App\Livewire\Peternak\SalesHistory;
use App\Livewire\Peternak\Wallet;
use App\Livewire\Peternak\FarmProfileSettings;
use App\Livewire\Peternak\AccountSettings;
use App\Livewire\Peternak\IncomingBookings;
use App\Livewire\Pelanggan\MyBookings;
use App\Livewire\Pelanggan\Dashboard;
use App\Livewire\Pelanggan\MyProfile;
use App\Livewire\Pelanggan\MyOrders;
use App\Livewire\Pelanggan\MyFavorites;
use App\Livewire\Pelanggan\MyAddresses;
use App\Livewire\Pelanggan\MyNotifications;
use App\Livewire\Customer\MyReviews;
use App\Livewire\Customer\SubmitProductReview;
use App\Livewire\Customer\SubmitFarmerReview;
use App\Livewire\Admin\ReviewModeration;
use App\Livewire\Admin\FarmerVerification;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Admin\TemplateController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Custom logout route (Fortify will use our custom controller for login)
Route::middleware('auth')->group(function () {
    Route::post('/logout', [CustomAuthenticatedSessionController::class, 'destroy'])->name('logout');
});

Route::get('/', Home::class)->name('home');
Route::get('/tentang-kami', About::class)->name('about');
Route::get('/kontak', Contact::class)->name('contact');
Route::get('/galeri', Gallery::class)->name('gallery');
Route::get('/checkout', Checkout::class)->name('checkout');

// Shop - Feed & Farm Supplies
Route::get('/shop', Shop::class)->name('shop');
Route::get('/shop/{slug}', ProductDetail::class)->name('shop.product');

// Stock Check Routes (for cart validation)
Route::get('/shop/check-stock/{productId}', function ($productId) {
    $product = \App\Models\Product::find($productId);

    if (!$product) {
        return response()->json([
            'available' => false,
            'stock' => 0,
            'message' => 'Produk tidak ditemukan',
        ]);
    }

    return response()->json([
        'available' => $product->stock > 0 && $product->status === 'available',
        'stock' => $product->stock,
        'status' => $product->stock_status,
        'message' => $product->stock > 0
            ? "Stok tersedia: {$product->stock} unit"
            : 'Stok habis',
    ]);
})->name('shop.check-stock');

Route::get('/shop/validate-cart/{productId}', function ($productId, \Illuminate\Http\Request $request) {
    $product = \App\Models\Product::find($productId);
    $requestedQty = (int) $request->query('qty', 1);

    if (!$product) {
        return response()->json([
            'valid' => false,
            'maxQty' => 0,
            'currentStock' => 0,
            'message' => 'Produk tidak ditemukan',
        ]);
    }

    $maxQty = $product->stock;

    if ($requestedQty > $maxQty) {
        return response()->json([
            'valid' => false,
            'maxQty' => $maxQty,
            'currentStock' => $product->stock,
            'message' => $maxQty > 0
                ? "Stok hanya {$maxQty} unit"
                : 'Stok habis',
        ]);
    }

    return response()->json([
        'valid' => true,
        'maxQty' => $maxQty,
        'currentStock' => $product->stock,
        'message' => 'OK',
    ]);
})->name('shop.validate-cart');

// FCM Token Routes (web - session auth)
Route::middleware(['auth'])->prefix('fcm-token')->group(function () {
    Route::post('/save', [App\Http\Controllers\Api\FcmController::class, 'saveToken'])->name('fcm-token.save');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function (Request $request) {
        if ($request->user()?->hasRole('admin') && Route::has('admin.dashboard')) {
            return to_route('admin.dashboard');
        }

        if ($request->user()?->hasRole('staf') && Route::has('staff.dashboard')) {
            return to_route('staff.dashboard');
        }

        if ($request->user()?->hasRole('peternak') && Route::has('peternak.dashboard')) {
            return to_route('peternak.dashboard');
        }

        return to_route('pelanggan.dashboard');
    })->name('dashboard');
});

Route::middleware(['auth', 'verified', 'check.admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::get('/', AdminDashboard::class)->name('dashboard');
        Route::get('/produk', ProductManagement::class)->name('products');
        Route::get('/produk/import', ImportProducts::class)->name('products.import');
        Route::get('/templates/{type}', [TemplateController::class, 'download'])->name('templates.download');
        Route::get('/pemesanan', OrderManagement::class)->name('orders');
        Route::get('/pelanggan', CustomerManagement::class)->name('customers');
        Route::get('/staf', StaffManagement::class)->name('staff');
        Route::get('/laporan-keuangan', FinancialReport::class)->name('finance');
        Route::get('/notifikasi', AdminNotifications::class)->name('notifications');
        Route::get('/pengaturan-toko', StoreSetting::class)->name('settings');
        Route::get('/pengaturan-akun', AdminAccountSettings::class)->name('account-settings');
        Route::get('/verifikasi-peternak', FarmerVerification::class)->name('farmer-verification');
    });

Route::middleware(['auth', 'verified', 'check.staff'])
    ->prefix('staff')
    ->name('staff.')
    ->group(function (): void {
        Route::view('/', 'dashboard')->name('dashboard');
        Route::get('/operasi', \App\Livewire\Staff\Operations::class)->name('operations');
    });

Route::middleware(['auth', 'verified', 'check.peternak'])
    ->prefix('peternak')
    ->name('peternak.')
    ->group(function (): void {
        Route::get('/', PeternakDashboard::class)->name('dashboard');

        // Ternak
        Route::get('/ternak', LivestockManagement::class)->name('livestock');
        Route::get('/ternak/tambah', LivestockManagement::class)->name('livestock.add');
        Route::get('/ternak/galeri', LivestockManagement::class)->name('livestock.gallery');

        // Pesanan
        Route::get('/pesanan', Orders::class)->name('orders');
        Route::get('/pesanan/baru', Orders::class)->name('orders.new');
        Route::get('/pesanan/riwayat', Orders::class)->name('orders.history');

        // Pengiriman
        Route::get('/pengiriman', Shipments::class)->name('shipments');
        Route::get('/pengiriman/riwayat', Shipments::class)->name('shipments.history');

        // Lainnya
        Route::get('/booking', IncomingBookings::class)->name('bookings');
        Route::get('/penjualan', SalesHistory::class)->name('sales');
        Route::get('/dompet', Wallet::class)->name('wallet');
        Route::get('/profil', FarmProfileSettings::class)->name('profile');
        Route::get('/notifikasi', Notifications::class)->name('notifications');
        Route::get('/pengaturan', AccountSettings::class)->name('settings');
        Route::get('/pengaturan/password', AccountSettings::class)->name('settings.password');
    });

Route::middleware(['auth', 'verified', 'check.pelanggan'])
    ->prefix('pelanggan')
    ->name('pelanggan.')
    ->group(function (): void {
        Route::get('/', Dashboard::class)->name('dashboard');
        Route::get('/profil', MyProfile::class)->name('profile');
        Route::get('/booking', MyBookings::class)->name('bookings');
        Route::get('/pesanan', MyOrders::class)->name('orders');
        Route::get('/favorit', MyFavorites::class)->name('favorites');
        Route::get('/alamat', MyAddresses::class)->name('addresses');
        Route::get('/notifikasi', MyNotifications::class)->name('notifications');
    });

Route::get('/midtrans-test', function () {

    \App\Services\MidtransService::init();

    return 'Midtrans Connected';
});

// Midtrans Webhook - harus di luar middleware auth
Route::post('/midtrans/notification', [PaymentController::class, 'notification'])->name('midtrans.notification');

// Midtrans Redirect Handler
Route::get('/checkout/success', function (\Illuminate\Http\Request $request) {
    // Redirect ke checkout page dengan parameter status
    return redirect()->route('checkout', [
        'payment_status' => $request->get('payment_status', 'unknown'),
        'order_id' => $request->get('order_id'),
    ]);
})->name('checkout.success');

require __DIR__ . '/settings.php';