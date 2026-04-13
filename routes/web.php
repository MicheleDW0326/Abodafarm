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
use Illuminate\Support\Facades\Route;

Route::get('/', Home::class)->name('home');
Route::get('/checkout', Checkout::class)->name('checkout');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

Route::middleware(['auth', 'verified', 'role:admin'])
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

require __DIR__.'/settings.php';
