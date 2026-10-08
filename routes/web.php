<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\HomeController;
use App\Http\Controllers\AdminController;


Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/portofolio', [HomeController::class, 'portfolioIndex'])->name('portfolio.index');
Route::get('/berita', [HomeController::class, 'newsIndex'])->name('news.index');
Route::get('/berita/{news}', [HomeController::class, 'showNews'])->name('news.show');
Route::post('/contact', [HomeController::class, 'contact'])->name('contact.send');
Route::get('/pembayaran/{payment:public_token}', [\App\Http\Controllers\PaymentController::class, 'show'])->name('payments.show');
Route::post('/midtrans/notification', [\App\Http\Controllers\PaymentController::class, 'notification'])->name('midtrans.notification');

Route::get('/admin', [AdminController::class, 'login'])->name('admin.login');
Route::post('/admin/login', [AdminController::class, 'authenticate'])->middleware('throttle:5,1')->name('admin.authenticate');
Route::middleware('portfolio.admin')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/dashboard/{section}', [AdminController::class, 'dashboard'])->whereIn('section', ['packages', 'projects', 'messages', 'profile', 'education', 'news', 'profile-photo', 'cv-panel', 'account', 'schedule', 'data-backup'])->name('dashboard.section');
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
