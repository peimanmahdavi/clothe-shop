<?php

use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DiscountController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\TransactionController as AdminTransactionController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Auth\PhoneAuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:global')->group(function () {
    Route::get('/', HomeController::class)->name('home');

    // Product catalog (public)
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');

    // Cart (works for guests via session and for logged-in users)
    Route::prefix('cart')->name('cart.')->group(function () {
        Route::get('/', [CartController::class, 'index'])->name('index');
        Route::post('/add', [CartController::class, 'add'])->name('add');
        Route::put('/items/{item}', [CartController::class, 'update'])->name('update');
        Route::delete('/items/{item}', [CartController::class, 'remove'])->name('remove');
        Route::delete('/clear', [CartController::class, 'clear'])->name('clear');
    });

    // Authentication routes (phone + OTP). Sensitive actions are throttled.
    Route::prefix('auth')->name('auth.')->group(function () {
        Route::get('/login', [PhoneAuthController::class, 'showLogin'])->name('login');
        Route::post('/send-otp', [PhoneAuthController::class, 'sendOtp'])
            ->middleware('throttle:otp')->name('send-otp');
        Route::post('/verify-otp', [PhoneAuthController::class, 'verifyOtp'])
            ->middleware('throttle:login')->name('verify-otp');
        Route::post('/login-password', [PhoneAuthController::class, 'loginWithPassword'])
            ->middleware('throttle:login')->name('login-password');
        Route::post('/logout', [PhoneAuthController::class, 'logout'])->name('logout');
        Route::post('/complete-profile', [PhoneAuthController::class, 'completeProfile'])->name('complete-profile');
    });

    // Customer area. 'auth.active' logs out and blocks banned accounts even
    // when a valid session already exists.
    Route::middleware(['auth', 'auth.active'])->prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'dashboard'])->name('dashboard');

        // Addresses
        Route::get('/addresses', [ProfileController::class, 'addresses'])->name('addresses');
        Route::post('/addresses', [ProfileController::class, 'storeAddress'])->name('addresses.store');
        Route::put('/addresses/{address}', [ProfileController::class, 'updateAddress'])->name('addresses.update');
        Route::delete('/addresses/{address}', [ProfileController::class, 'destroyAddress'])->name('addresses.destroy');
        Route::post('/addresses/{address}/default', [ProfileController::class, 'setDefaultAddress'])->name('addresses.default');

        // Checkout / new order
        Route::get('/checkout', [ProfileController::class, 'checkout'])->name('checkout');
        Route::post('/checkout/discount', [ProfileController::class, 'applyDiscount'])->name('checkout.discount');

        // Orders
        Route::get('/orders', [ProfileController::class, 'orders'])->name('orders');
        Route::post('/orders', [ProfileController::class, 'storeOrder'])
            ->middleware('throttle:orders')->name('orders.store');
        Route::get('/orders/{order}', [ProfileController::class, 'showOrder'])->name('orders.show');
        Route::get('/orders/{order}/upload-receipt', [ProfileController::class, 'showUploadReceipt'])->name('orders.receipt');
        Route::post('/orders/{order}/upload-receipt', [ProfileController::class, 'uploadReceipt'])
            ->middleware('throttle:receipts')->name('orders.receipt.store');
        Route::post('/orders/{order}/payment/callback', [ProfileController::class, 'paymentCallback'])
            ->middleware('throttle:orders')->name('orders.payment.callback');

        // Profile
        Route::put('/profile', [ProfileController::class, 'updateProfile'])->name('update');

        // Account security (password add/change/remove)
        Route::get('/security', [ProfileController::class, 'showSecurity'])->name('security');
        Route::put('/security/password', [ProfileController::class, 'updatePassword'])
            ->middleware('throttle:password-reset')->name('security.password');
        Route::delete('/security/password', [ProfileController::class, 'removePassword'])
            ->middleware('throttle:password-reset')->name('security.password.remove');
    });

    // Admin area: authentication, active status and admin privilege are all
    // required. Every write here is CSRF-protected by the web middleware group.
    Route::prefix('admin')->name('admin.')->middleware(['auth', 'auth.active', 'admin'])->group(function () {
        Route::get('/', DashboardController::class)->name('dashboard');

        Route::get('orders', [AdminOrderController::class, 'index'])->name('orders.index');
        Route::get('orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
        Route::put('orders/{order}', [AdminOrderController::class, 'update'])->name('orders.update');
        Route::post('orders/{order}/approve-transaction', [AdminOrderController::class, 'approveTransaction'])->name('orders.approve-transaction');
        Route::post('orders/{order}/reject-transaction', [AdminOrderController::class, 'rejectTransaction'])->name('orders.reject-transaction');

        Route::get('transactions', [AdminTransactionController::class, 'index'])->name('transactions.index');
        Route::put('transactions/{transaction}', [AdminTransactionController::class, 'update'])->name('transactions.update');

        Route::resource('products', AdminProductController::class)->except(['show']);
        Route::delete('products/{product}/images/{image}', [AdminProductController::class, 'destroyImage'])
            ->name('products.images.destroy');

        Route::resource('categories', CategoryController::class)->except(['show', 'create']);
        Route::resource('brands', BrandController::class)->except(['show', 'create']);
        Route::resource('discounts', DiscountController::class)->except(['show', 'create']);

        // User management (self-lockout protections live in the controller).
        Route::resource('users', AdminUserController::class)->except(['show']);
        Route::post('users/{user}/toggle-status', [AdminUserController::class, 'toggleStatus'])
            ->name('users.toggle-status');

        // Customer carts
        Route::get('carts', [App\Http\Controllers\Admin\CartController::class, 'index'])->name('carts.index');
        Route::get('carts/{cart}', [App\Http\Controllers\Admin\CartController::class, 'show'])->name('carts.show');
        Route::post('carts/{cart}/items', [App\Http\Controllers\Admin\CartController::class, 'addItem'])->name('carts.items.add');
        Route::put('carts/{cart}/items/{item}', [App\Http\Controllers\Admin\CartController::class, 'updateItem'])->name('carts.items.update');
        Route::delete('carts/{cart}/items/{item}', [App\Http\Controllers\Admin\CartController::class, 'removeItem'])->name('carts.items.remove');
        Route::delete('carts/{cart}', [App\Http\Controllers\Admin\CartController::class, 'clear'])->name('carts.clear');
    });
});
