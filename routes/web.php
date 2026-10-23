<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Cashier\CashierController;
use App\Http\Controllers\Customer\DisplayController;
use App\Http\Controllers\Customer\MenuController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/menu');

Route::get('/menu', [MenuController::class, 'index'])->name('menu');
Route::post('/menu/pesan', [MenuController::class, 'store'])->middleware('throttle:10,1')->name('menu.store');
Route::get('/menu/pesanan/{order}', [MenuController::class, 'show'])->name('menu.order');
Route::get('/layar', DisplayController::class)->name('display');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/akun/password', [PasswordController::class, 'edit'])->name('password.edit');
    Route::put('/akun/password', [PasswordController::class, 'update'])->name('password.update');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::resource('categories', CategoryController::class)->except('show');
    Route::resource('products', ProductController::class)->except('show');
    Route::resource('users', UserController::class)->except('show');
});

Route::middleware(['auth', 'staff'])->prefix('kasir')->name('cashier.')->group(function () {
    Route::get('/', [CashierController::class, 'index'])->name('index');
    Route::get('/pesanan-masuk', [CashierController::class, 'pending'])->name('pending');
    Route::post('/pesanan', [CashierController::class, 'store'])->name('store');
    Route::post('/pesanan/{order}/bayar', [CashierController::class, 'pay'])->name('pay');
    Route::post('/pesanan/{order}/batal', [CashierController::class, 'cancel'])->name('cancel');
    Route::get('/pesanan/{order}/struk', [CashierController::class, 'receipt'])->name('receipt');
});
