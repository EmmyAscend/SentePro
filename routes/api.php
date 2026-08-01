<?php

use App\Http\Controllers\Api\PaymentLinkController;
use App\Http\Controllers\Api\PaymentTransactionController;
use App\Http\Controllers\Api\WalletController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    Route::get('/wallet', [WalletController::class, 'show'])->name('api.wallet.show');

    Route::get('/payment-links', [PaymentLinkController::class, 'index'])->name('api.payment-links.index');
    Route::post('/payment-links', [PaymentLinkController::class, 'store'])->name('api.payment-links.store');
    Route::get('/payment-links/{paymentLink}', [PaymentLinkController::class, 'show'])->name('api.payment-links.show');

    Route::get('/transactions', [PaymentTransactionController::class, 'index'])->name('api.transactions.index');
    Route::get('/transactions/{transaction}', [PaymentTransactionController::class, 'show'])->name('api.transactions.show');
});
