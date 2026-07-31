<?php

use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\BusinessManagementController;
use App\Http\Controllers\BusinessRegistrationController;
use App\Http\Controllers\BusinessReviewController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\FeeBreakdownController;
use App\Http\Controllers\FeeStructureController;
use App\Http\Controllers\GatewayProviderController;
use App\Http\Controllers\PaymentLinkController;
use App\Http\Controllers\PaymentTransactionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicCheckoutController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\RefundController;
use App\Http\Controllers\SettlementController;
use App\Http\Controllers\SettlementMethodController;
use App\Http\Controllers\SettlementReviewController;
use App\Http\Controllers\StaffManagementController;
use App\Http\Controllers\WalletMonitoringController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\Webhooks\PesapalWebhookController;
use App\Http\Controllers\Webhooks\YoPaymentsWebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome', [
        'headline' => 'Collect Payments. Settle Faster. Grow Your Business.',
    ]);
});

Route::get('/pay/{paymentLink}', [PublicCheckoutController::class, 'show'])
    ->name('checkout.show');

Route::get('/pay/{paymentLink}/status', [PublicCheckoutController::class, 'status'])
    ->name('checkout.status');

Route::post('/pay/{paymentLink}', [PublicCheckoutController::class, 'store'])
    ->name('checkout.store');

Route::post('/webhooks/pesapal/{gatewayProvider}', [PesapalWebhookController::class, 'receive'])
    ->name('webhooks.pesapal.receive');

Route::post('/webhooks/yo-payments/{gatewayProvider}/success', [YoPaymentsWebhookController::class, 'success'])
    ->name('webhooks.yo-payments.success');

Route::post('/webhooks/yo-payments/{gatewayProvider}/failure', [YoPaymentsWebhookController::class, 'failure'])
    ->name('webhooks.yo-payments.failure');

Route::get('/receipts/{receipt}', [ReceiptController::class, 'show'])
    ->name('receipts.show');

Route::get('/receipts/{receipt}/verify', [ReceiptController::class, 'verify'])
    ->name('receipts.verify');

Route::get('/receipts/{receipt}/qr-code', [ReceiptController::class, 'qrCode'])
    ->name('receipts.qr-code');

Route::get('/business/register', [BusinessRegistrationController::class, 'index'])
    ->name('business.register');

Route::post('/business/register', [BusinessRegistrationController::class, 'store'])
    ->name('business.register.store');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/settlements', [SettlementController::class, 'index'])->name('settlements.index');
    Route::post('/settlements', [SettlementController::class, 'store'])->name('settlements.store');
    Route::post('/admin/settlements/{settlement}/complete', [SettlementReviewController::class, 'complete'])->name('admin.settlements.complete');
    Route::post('/admin/settlements/{settlement}/reject', [SettlementReviewController::class, 'reject'])->name('admin.settlements.reject');
    Route::get('/admin/settlement-methods', [SettlementMethodController::class, 'index'])->name('admin.settlement-methods');
    Route::post('/admin/settlement-methods', [SettlementMethodController::class, 'store'])->name('admin.settlement-methods.store');
    Route::put('/admin/settlement-methods/{settlementMethod}', [SettlementMethodController::class, 'update'])->name('admin.settlement-methods.update');
    Route::post('/admin/businesses/{business}/review', [BusinessReviewController::class, 'review'])->name('admin.businesses.review');
    Route::get('/admin/staff', [StaffManagementController::class, 'index'])->name('admin.staff');
    Route::post('/admin/staff', [StaffManagementController::class, 'store'])->name('admin.staff.store');
    Route::get('/payment-links', [PaymentLinkController::class, 'index'])->name('payment-links.index');
    Route::post('/payment-links', [PaymentLinkController::class, 'store'])->name('payment-links.store');
    Route::get('/gateways', [GatewayProviderController::class, 'index'])->name('gateways.index');
    Route::post('/gateways', [GatewayProviderController::class, 'store'])->name('gateways.store');
    Route::get('/transactions', [PaymentTransactionController::class, 'index'])->name('transactions.index');
    Route::post('/transactions', [PaymentTransactionController::class, 'store'])->name('transactions.store');
    Route::post('/transactions/{transaction}/refund', [RefundController::class, 'store'])->name('transactions.refund');
    Route::get('/receipts', [ReceiptController::class, 'index'])->name('receipts.index');
    Route::get('/webhooks', [WebhookController::class, 'index'])->name('webhooks.index');
    Route::post('/webhooks', [WebhookController::class, 'store'])->name('webhooks.store');
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
    Route::get('/fee-breakdowns', [FeeBreakdownController::class, 'index'])->name('fee-breakdowns.index');
    Route::post('/fee-breakdowns', [FeeBreakdownController::class, 'store'])->name('fee-breakdowns.store');
    Route::get('/admin/fee-structures', [FeeStructureController::class, 'index'])->name('admin.fee-structures');
    Route::post('/admin/fee-structures', [FeeStructureController::class, 'store'])->name('admin.fee-structures.store');
    Route::put('/admin/fee-structures/{feeStructure}', [FeeStructureController::class, 'update'])->name('admin.fee-structures.update');
    Route::get('/admin/audit-logs', [AuditLogController::class, 'index'])->name('admin.audit-logs');
    Route::get('/admin/wallet-monitoring', [WalletMonitoringController::class, 'index'])->name('admin.wallet-monitoring');
    Route::get('/admin/businesses', [BusinessManagementController::class, 'index'])->name('admin.businesses.index');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
