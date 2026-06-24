<?php

use App\Livewire\Admin\CustomerManagement;
use App\Livewire\Admin\Dashboard as AdminDashboard;
use App\Livewire\Admin\FinancialReport;
use App\Livewire\Admin\OrderManagement;
use App\Livewire\Admin\ProductManagement;
use App\Livewire\Admin\StaffManagement;
use App\Livewire\Admin\StoreSetting;
use App\Livewire\Checkout;
use App\Livewire\Home;
use App\Livewire\About;
use App\Livewire\Contact;
use App\Livewire\Peternak\Dashboard as PeternakDashboard;
use App\Livewire\Peternak\LivestockManagement;
use App\Livewire\Peternak\Orders;
use App\Livewire\Peternak\Shipments;
use App\Livewire\Peternak\SalesHistory;
use App\Livewire\Peternak\Wallet;
use App\Livewire\Peternak\FarmProfileSettings;
use App\Livewire\Peternak\Notifications;
use App\Livewire\Peternak\AccountSettings;
use App\Livewire\Peternak\IncomingBookings;
use App\Livewire\Pelanggan\MyBookings;
use App\Livewire\Pelanggan\Dashboard;
use App\Livewire\Pelanggan\MyProfile;
use App\Livewire\Pelanggan\MyOrders;
use App\Livewire\Pelanggan\MyFavorites;
use App\Livewire\Pelanggan\MyAddresses;
use App\Livewire\Pelanggan\MyNotifications;
use App\Http\Controllers\Api\PaymentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', Home::class)->name('home');
Route::get('/tentang-kami', About::class)->name('about');
Route::get('/kontak', Contact::class)->name('contact');
Route::get('/checkout', Checkout::class)->name('checkout');

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
        Route::get('/pemesanan', OrderManagement::class)->name('orders');
        Route::get('/pelanggan', CustomerManagement::class)->name('customers');
        Route::get('/staf', StaffManagement::class)->name('staff');
        Route::get('/laporan-keuangan', FinancialReport::class)->name('finance');
        Route::get('/pengaturan-toko', StoreSetting::class)->name('settings');
    });

Route::middleware(['auth', 'verified', 'check.staff'])
    ->prefix('staff')
    ->name('staff.')
    ->group(function (): void {
        Route::view('/', 'dashboard')->name('dashboard');
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