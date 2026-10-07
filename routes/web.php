<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\HomeController;
use App\Http\Controllers\AdminController;


Route::get('/', [HomeController::class, 'index'])->name('home');
Route::post('/contact', [HomeController::class, 'contact'])->name('contact.send');

Route::get('/admin', [AdminController::class, 'login'])->name('admin.login');
Route::post('/admin/login', [AdminController::class, 'authenticate'])->middleware('throttle:5,1')->name('admin.authenticate');
Route::middleware('portfolio.admin')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::post('/projects', [AdminController::class, 'storeProject'])->name('projects.store');
    Route::delete('/projects/{project}', [AdminController::class, 'deleteProject'])->name('projects.delete');
    Route::post('/services', [AdminController::class, 'storeService'])->name('services.store');
    Route::put('/services/{service}', [AdminController::class, 'updateService'])->name('services.update');
    Route::delete('/services/{service}', [AdminController::class, 'deleteService'])->name('services.delete');
    Route::post('/cv', [AdminController::class, 'uploadCv'])->name('cv.upload');
    Route::post('/profile-photo', [AdminController::class, 'uploadProfilePhoto'])->name('profile-photo.upload');
    Route::post('/logout', [AdminController::class, 'logout'])->name('logout');
});
