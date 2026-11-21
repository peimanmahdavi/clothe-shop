<?php

use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProfileController;
use Illuminate\Support\Facades\Route;

// Public authentication (phone + OTP)
Route::prefix('auth')->name('api.auth.')->group(function () {
    Route::post('/send-otp', [AuthController::class, 'sendOtp'])->middleware('throttle:otp')->name('send-otp');
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp'])->middleware('throttle:login')->name('verify-otp');
});

// Protected routes (Bearer token + active account)
Route::middleware(['auth.api', 'auth.active'])->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout'])->name('api.auth.logout');
    Route::post('/auth/complete-profile', [AuthController::class, 'completeProfile'])->name('api.auth.complete-profile');

    Route::get('/profile', [ProfileController::class, 'show'])->name('api.profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('api.profile.update');

    Route::get('/addresses', [AddressController::class, 'index'])->name('api.addresses.index');
    Route::post('/addresses', [AddressController::class, 'store'])->name('api.addresses.store');
    Route::put('/addresses/{address}', [AddressController::class, 'update'])->name('api.addresses.update');
    Route::delete('/addresses/{address}', [AddressController::class, 'destroy'])->name('api.addresses.destroy');
    Route::post('/addresses/{address}/default', [AddressController::class, 'setDefault'])->name('api.addresses.default');

    Route::get('/orders', [OrderController::class, 'index'])->name('api.orders.index');
    Route::post('/orders', [OrderController::class, 'store'])->middleware('throttle:orders')->name('api.orders.store');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('api.orders.show');
});
