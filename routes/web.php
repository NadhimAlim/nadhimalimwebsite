<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\HomeController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\MarketplaceController;
use App\Http\Controllers\MarketplaceAccountController;


Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/portofolio', [HomeController::class, 'portfolioIndex'])->name('portfolio.index');
Route::get('/berita', [HomeController::class, 'newsIndex'])->name('news.index');
Route::get('/berita/{news}', [HomeController::class, 'showNews'])->name('news.show');
Route::post('/contact', [HomeController::class, 'contact'])->name('contact.send');
Route::get('/pembayaran/{payment:public_token}', [\App\Http\Controllers\PaymentController::class, 'show'])->name('payments.show');
Route::post('/midtrans/notification', [\App\Http\Controllers\PaymentController::class, 'notification'])->name('midtrans.notification');
Route::get('/toko', [MarketplaceController::class, 'index'])->name('marketplace.index');
Route::get('/akun/masuk', [MarketplaceAccountController::class, 'login'])->name('marketplace.account.login');
Route::post('/akun/masuk', [MarketplaceAccountController::class, 'authenticate'])->middleware('throttle:8,1')->name('marketplace.account.authenticate');
Route::get('/akun/daftar', [MarketplaceAccountController::class, 'register'])->name('marketplace.account.register');
Route::post('/akun/daftar', [MarketplaceAccountController::class, 'store'])->middleware('throttle:5,1')->name('marketplace.account.store');
Route::post('/akun/keluar', [MarketplaceAccountController::class, 'logout'])->name('marketplace.account.logout');
Route::get('/akun/pesanan', [MarketplaceAccountController::class, 'orders'])->name('marketplace.account.orders');
Route::get('/toko/{product:slug}', [MarketplaceController::class, 'show'])->name('marketplace.show');
Route::post('/toko/{product:slug}/pesan', [MarketplaceController::class, 'order'])->middleware('throttle:10,1')->name('marketplace.order');
Route::get('/keranjang', [MarketplaceController::class, 'cart'])->name('marketplace.cart');
Route::post('/keranjang/{product:slug}', [MarketplaceController::class, 'addToCart'])->name('marketplace.cart.add');
Route::patch('/keranjang/{product:slug}', [MarketplaceController::class, 'updateCart'])->name('marketplace.cart.update');
Route::delete('/keranjang/{product:slug}', [MarketplaceController::class, 'removeFromCart'])->name('marketplace.cart.remove');
Route::get('/checkout', [MarketplaceController::class, 'checkoutCart'])->name('marketplace.checkout.cart');
Route::post('/checkout', [MarketplaceController::class, 'placeOrder'])->middleware('throttle:10,1')->name('marketplace.checkout.place');
Route::get('/pesanan/{order:public_token}', [MarketplaceController::class, 'checkout'])->name('marketplace.checkout');
Route::post('/pesanan/{order:public_token}/bukti', [MarketplaceController::class, 'uploadPaymentProof'])->middleware('throttle:5,1')->name('marketplace.payment-proof');

Route::get('/admin', [AdminController::class, 'login'])->name('admin.login');
Route::post('/admin/login', [AdminController::class, 'authenticate'])->middleware('throttle:5,1')->name('admin.authenticate');
Route::middleware('portfolio.admin')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/dashboard/{section}', [AdminController::class, 'dashboard'])->whereIn('section', ['packages', 'projects', 'messages', 'profile', 'education', 'news', 'profile-photo', 'cv-panel', 'account', 'schedule', 'data-backup', 'marketplace'])->name('dashboard.section');
    Route::put('/account/profile', [AdminController::class, 'updateAdminProfile'])->name('account.profile.update');
    Route::put('/account/password', [AdminController::class, 'changeAdminPassword'])->name('account.password.update');
    Route::post('/work-tasks', [AdminController::class, 'storeWorkTask'])->name('work-tasks.store');
    Route::put('/work-tasks/{workTask}', [AdminController::class, 'updateWorkTask'])->name('work-tasks.update');
    Route::delete('/work-tasks/{workTask}', [AdminController::class, 'deleteWorkTask'])->name('work-tasks.delete');
    Route::get('/work-tasks/export', [AdminController::class, 'exportWorkTasks'])->name('work-tasks.export');
    Route::post('/work-tasks/{workTask}/payment-links', [\App\Http\Controllers\PaymentController::class, 'createLink'])->name('work-tasks.payment-links.store');
    Route::get('/backup/download', [AdminController::class, 'downloadBackup'])->name('backup.download');
    Route::post('/backup/restore', [AdminController::class, 'restoreBackup'])->name('backup.restore');
    Route::post('/projects', [AdminController::class, 'storeProject'])->name('projects.store');
    Route::post('/marketplace/products', [MarketplaceController::class, 'store'])->name('marketplace.products.store');
    Route::put('/marketplace/products/{product}', [MarketplaceController::class, 'update'])->name('marketplace.products.update');
    Route::delete('/marketplace/products/{product}', [MarketplaceController::class, 'destroy'])->name('marketplace.products.destroy');
    Route::put('/marketplace/orders/{order}', [MarketplaceController::class, 'updateOrder'])->name('marketplace.orders.update');
    Route::put('/marketplace/shipping-fee', [MarketplaceController::class, 'updateShippingFee'])->name('marketplace.shipping-fee.update');
    Route::put('/marketplace/payment-settings', [MarketplaceController::class, 'updatePaymentSettings'])->name('marketplace.payment-settings.update');
    Route::get('/marketplace/orders/{order}/proof', [MarketplaceController::class, 'showPaymentProof'])->name('marketplace.orders.proof');
    Route::put('/marketplace/orders/{order}/payment-review', [MarketplaceController::class, 'reviewPayment'])->name('marketplace.orders.payment-review');
    Route::put('/projects/{project}', [AdminController::class, 'updateProject'])->name('projects.update');
    Route::delete('/projects/{project}', [AdminController::class, 'deleteProject'])->name('projects.delete');
    Route::post('/services', [AdminController::class, 'storeService'])->name('services.store');
    Route::put('/services/{service}', [AdminController::class, 'updateService'])->name('services.update');
    Route::delete('/services/{service}', [AdminController::class, 'deleteService'])->name('services.delete');
    Route::delete('/messages/{contactMessage}', [AdminController::class, 'deleteContactMessage'])->name('messages.delete');
    Route::put('/messages/{contactMessage}', [AdminController::class, 'updateContactMessageStatus'])->name('messages.status');
    Route::post('/profile/preview', [AdminController::class, 'previewProfile'])->name('profile.preview');
    Route::post('/profile/publish', [AdminController::class, 'publishProfilePreview'])->name('profile.publish');
    Route::delete('/profile/preview', [AdminController::class, 'discardProfilePreview'])->name('profile.preview.discard');
    Route::patch('/projects/{project}/order', [AdminController::class, 'moveProject'])->name('projects.order');
    Route::put('/education/{education}', [AdminController::class, 'updateEducation'])->name('education.update');
    Route::post('/news', [AdminController::class, 'storeNews'])->name('news.store');
    Route::put('/news/{news}', [AdminController::class, 'updateNews'])->name('news.update');
    Route::delete('/news/{news}', [AdminController::class, 'deleteNews'])->name('news.delete');
    Route::post('/cv', [AdminController::class, 'uploadCv'])->name('cv.upload');
    Route::post('/profile-photo', [AdminController::class, 'uploadProfilePhoto'])->name('profile-photo.upload');
    Route::post('/logout', [AdminController::class, 'logout'])->name('logout');
});
