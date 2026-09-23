<?php

use App\Http\Controllers\Admin\BusinessSettingController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ServiceController as AdminServiceController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderFileController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ServiceController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/layanan', [ServiceController::class, 'index'])->name('services.index');
Route::get('/layanan/{service:slug}', [ServiceController::class, 'show'])->name('services.show');
Route::get('/kontak', [ContactController::class, 'index'])->name('kontak');

Route::post('/midtrans/notification', [PaymentController::class, 'notification'])->name('midtrans.notification');

Route::get('/keranjang', [CartController::class, 'index'])->name('cart.index');
Route::post('/keranjang', [CartController::class, 'store'])->name('cart.store');
Route::patch('/keranjang/{service}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/keranjang/{service}', [CartController::class, 'destroy'])->name('cart.destroy');

Route::middleware('auth')->group(function () {
    Route::get('/checkout', [CheckoutController::class, 'create'])->name('checkout.create');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

    Route::get('/pesanan', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/pesanan/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/pesanan/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
    Route::post('/pesanan/{order}/pay', [PaymentController::class, 'create'])->name('orders.pay');
    Route::post('/pesanan/{order}/payment/skip', [PaymentController::class, 'skip'])->name('orders.payment.skip');
    Route::get('/pesanan/{order}/payment/status', [PaymentController::class, 'status'])->name('orders.payment.status');
    Route::get('/pesanan/{order}/payment/result', [PaymentController::class, 'result'])->name('orders.payment.result');
    Route::post('/pesanan/{order}/files', [OrderFileController::class, 'store'])->name('orders.files.store');
    Route::get('/pesanan/{order}/files/{file}/download', [OrderFileController::class, 'download'])->name('orders.files.download');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/laporan', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.status');
    Route::resource('services', AdminServiceController::class)->except('show');
    Route::post('services/{service}/toggle', [AdminServiceController::class, 'toggle'])->name('services.toggle');
    Route::resource('categories', AdminCategoryController::class)->except('show');
    Route::post('categories/{category}/toggle', [AdminCategoryController::class, 'toggle'])->name('categories.toggle');
    Route::get('/info-usaha', [BusinessSettingController::class, 'edit'])->name('business-settings.edit');
    Route::put('/info-usaha', [BusinessSettingController::class, 'update'])->name('business-settings.update');
});

require __DIR__.'/auth.php';
