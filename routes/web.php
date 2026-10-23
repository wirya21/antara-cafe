<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Customer\MenuController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/menu');

Route::get('/menu', [MenuController::class, 'index'])->name('menu');
Route::post('/menu/pesan', [MenuController::class, 'store'])->middleware('throttle:10,1')->name('menu.store');
Route::get('/menu/pesanan/{order}', [MenuController::class, 'show'])->name('menu.order');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::resource('categories', CategoryController::class)->except('show');
    Route::resource('products', ProductController::class)->except('show');
});
