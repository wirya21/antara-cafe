<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\IngredientController;
use App\Http\Controllers\Admin\MemberController;
use App\Http\Controllers\Admin\OrderBoardController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\PromotionController;
use App\Http\Controllers\Admin\RecipeController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Cashier\CashierController;
use App\Http\Controllers\Customer\DisplayController;
use App\Http\Controllers\Customer\MenuController;
use App\Http\Controllers\Kafehub\AuthController as PanelAuthController;
use App\Http\Controllers\Kafehub\PanelController;
use App\Http\Controllers\LandingController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LandingController::class, 'home']);
Route::get('/kafehub', [LandingController::class, 'show'])->name('kafehub');

Route::get('/menu', [MenuController::class, 'index'])->name('menu');
Route::post('/menu/pesan', [MenuController::class, 'store'])->middleware('throttle:10,1')->name('menu.store');
Route::get('/menu/voucher', [MenuController::class, 'voucher'])->middleware('throttle:30,1')->name('menu.voucher');
Route::get('/menu/riwayat', [MenuController::class, 'history'])->name('menu.history');
Route::get('/menu/riwayat/data', [MenuController::class, 'historyData'])->middleware('throttle:60,1')->name('menu.history.data');
Route::get('/menu/pesanan/{order}', [MenuController::class, 'show'])->name('menu.order');
Route::get('/menu/pesanan/{order}/status', [MenuController::class, 'status'])->middleware('throttle:60,1')->name('menu.order.status');
Route::post('/menu/pesanan/{order}/metode', [MenuController::class, 'changeMethod'])->middleware('throttle:10,1')->name('menu.order.method');
Route::post('/menu/pesanan/{order}/bukti', [MenuController::class, 'uploadProof'])->middleware('throttle:10,1')->name('menu.order.proof');
Route::get('/layar', DisplayController::class)->name('display');

// Panel pengelola kafehub.id: akun dan login sendiri, terpisah dari admin kafe.
Route::prefix('panel')->name('panel.')->group(function () {
    Route::middleware('guest:kafehub')->group(function () {
        Route::get('masuk', [PanelAuthController::class, 'show'])->name('login');
        Route::post('masuk', [PanelAuthController::class, 'login'])->middleware('throttle:10,1');
    });
    Route::middleware('auth:kafehub')->group(function () {
        Route::get('/', [PanelController::class, 'edit'])->name('edit');
        Route::put('/', [PanelController::class, 'update'])->name('update');
        Route::put('password', [PanelAuthController::class, 'password'])->name('password');
        Route::post('keluar', [PanelAuthController::class, 'logout'])->name('logout');
    });
});

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

    Route::get('laporan', [ReportController::class, 'index'])->name('reports.index');
    Route::get('laporan/export', [ReportController::class, 'export'])->name('reports.export');

    Route::get('pesanan', [OrderBoardController::class, 'index'])->name('board.index');
    Route::get('pesanan/jumlah', [OrderBoardController::class, 'counts'])->name('board.counts');

    Route::get('transaksi', [OrderController::class, 'index'])->name('orders.index');
    Route::get('transaksi/export', [OrderController::class, 'export'])->name('orders.export');
    Route::get('transaksi/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('transaksi/{order}/void', [OrderController::class, 'void'])->name('orders.void');

    Route::resource('promo', PromotionController::class)->except('show')->names('promotions')->parameters(['promo' => 'promotion']);
    Route::resource('member', MemberController::class)->only(['index', 'edit', 'update', 'destroy'])->names('members');

    Route::resource('stok', IngredientController::class)->names('ingredients')->parameters(['stok' => 'ingredient']);
    Route::get('resep', [RecipeController::class, 'index'])->name('recipes.index');
    Route::post('stok/{ingredient}/catat', [IngredientController::class, 'move'])->name('ingredients.move');

    Route::get('pengaturan', [SettingController::class, 'edit'])->name('settings.edit');
    Route::put('pengaturan', [SettingController::class, 'update'])->name('settings.update');
    Route::post('pengaturan/tes-whatsapp', [SettingController::class, 'testWhatsApp'])->name('settings.whatsapp-test');
});

Route::middleware(['auth', 'staff'])->prefix('kasir')->name('cashier.')->group(function () {
    Route::get('/', [CashierController::class, 'index'])->name('index');
    Route::get('/pesanan-masuk', [CashierController::class, 'pending'])->name('pending');
    Route::get('/notifikasi', [CashierController::class, 'alerts'])->name('alerts');
    Route::post('/mode-ramai', [CashierController::class, 'toggleBusy'])->name('busy');
    Route::post('/pesanan/{order}/selesai', [CashierController::class, 'complete'])->name('complete');
    Route::get('/pesanan/{order}/bukti', [CashierController::class, 'proof'])->name('proof');
    Route::post('/pesanan', [CashierController::class, 'store'])->name('store');
    Route::post('/pesanan/{order}/bayar', [CashierController::class, 'pay'])->name('pay');
    Route::post('/pesanan/{order}/batal', [CashierController::class, 'cancel'])->name('cancel');
    Route::get('/pesanan/{order}/struk', [CashierController::class, 'receipt'])->name('receipt');
});
