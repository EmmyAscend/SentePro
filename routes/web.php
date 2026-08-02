<?php

use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\ApiKeyController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\BusinessManagementController;
use App\Http\Controllers\BusinessRegistrationController;
use App\Http\Controllers\BusinessReviewController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DisputeController;
use App\Http\Controllers\DisputeReviewController;
use App\Http\Controllers\FeeBreakdownController;
use App\Http\Controllers\FeeStructureController;
use App\Http\Controllers\GatewayMonitoringController;
use App\Http\Controllers\GatewayProviderController;
use App\Http\Controllers\KnowledgeBaseController;
use App\Http\Controllers\LandingPageContentController;
use App\Http\Controllers\LegalPageController;
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
use App\Http\Controllers\SupportTicketController;
use App\Http\Controllers\SupportTicketMessageController;
use App\Http\Controllers\SupportTicketReviewController;
use App\Http\Controllers\WalletMonitoringController;
use App\Http\Controllers\WalletTransferController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\Webhooks\PesapalWebhookController;
use App\Http\Controllers\Webhooks\YoPaymentsWebhookController;
use App\Models\LandingPageContent;
use App\Models\LegalPage;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome', [
        'content' => LandingPageContent::current(),
    ]);
});

Route::get('/legal/{slug}', [LegalPageController::class, 'show'])
    ->whereIn('slug', LegalPage::SLUGS)
    ->name('legal.show');

Route::get('/pay/{paymentLink}', [PublicCheckoutController::class, 'show'])
    ->name('checkout.show');

Route::get('/pay/{paymentLink}/status', [PublicCheckoutController::class, 'status'])
    ->name('checkout.status');

Route::get('/pay/{paymentLink}/qr-code', [PublicCheckoutController::class, 'qrCode'])
    ->name('checkout.qr-code');

Route::post('/pay/{paymentLink}', [PublicCheckoutController::class, 'store'])
    ->name('checkout.store');

Route::post('/webhooks/pesapal/{gatewayProvider}', [PesapalWebhookController::class, 'receive'])
    ->name('webhooks.pesapal.receive');

Route::post('/webhooks/yo-payments/{gatewayProvider}/success', [YoPaymentsWebhookController::class, 'success'])
    ->name('webhooks.yo-payments.success');

Route::post('/webhooks/yo-payments/{gatewayProvider}/failure', [YoPaymentsWebhookController::class, 'failure'])
    ->name('webhooks.yo-payments.failure');

// Registered before the {receipt} wildcard below — both are one-segment
// paths under /receipts/, and route matching is registration-order, so
// /receipts/export would otherwise be swallowed by receipts.show.
Route::get('/receipts/export', [ReceiptController::class, 'export'])
    ->middleware(['auth', 'verified'])
    ->name('receipts.export');

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
    Route::get('/settlements/export', [SettlementController::class, 'export'])->name('settlements.export');
    Route::post('/admin/settlements/{settlement}/complete', [SettlementReviewController::class, 'complete'])->name('admin.settlements.complete');
    Route::post('/admin/settlements/{settlement}/reject', [SettlementReviewController::class, 'reject'])->name('admin.settlements.reject');
    Route::post('/admin/settlements/{settlement}/reverse', [SettlementReviewController::class, 'reverse'])->name('admin.settlements.reverse');
    Route::post('/admin/settlements/{settlement}/retry', [SettlementReviewController::class, 'retry'])->name('admin.settlements.retry');
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
    Route::post('/gateways/{gatewayProvider}/test', [GatewayProviderController::class, 'test'])->name('gateways.test');
    Route::get('/admin/gateway-monitoring', [GatewayMonitoringController::class, 'index'])->name('admin.gateway-monitoring');
    Route::get('/transactions', [PaymentTransactionController::class, 'index'])->name('transactions.index');
    Route::post('/transactions', [PaymentTransactionController::class, 'store'])->name('transactions.store');
    Route::get('/transactions/export', [PaymentTransactionController::class, 'export'])->name('transactions.export');
    Route::post('/transactions/{transaction}/refund', [RefundController::class, 'store'])->name('transactions.refund');
    Route::post('/transactions/{transaction}/disputes', [DisputeController::class, 'store'])->name('disputes.store');
    Route::get('/disputes', [DisputeController::class, 'index'])->name('disputes.index');
    Route::get('/disputes/{dispute}', [DisputeController::class, 'show'])->name('disputes.show');
    Route::post('/admin/disputes/{dispute}/resolve', [DisputeReviewController::class, 'resolve'])->name('admin.disputes.resolve');
    Route::post('/admin/disputes/{dispute}/reject', [DisputeReviewController::class, 'reject'])->name('admin.disputes.reject');
    Route::get('/api-keys', [ApiKeyController::class, 'index'])->name('api-keys.index');
    Route::post('/api-keys', [ApiKeyController::class, 'store'])->name('api-keys.store');
    Route::delete('/api-keys/{token}', [ApiKeyController::class, 'destroy'])->name('api-keys.destroy');
    Route::get('/receipts', [ReceiptController::class, 'index'])->name('receipts.index');
    Route::get('/webhooks', [WebhookController::class, 'index'])->name('webhooks.index');
    Route::post('/webhooks', [WebhookController::class, 'store'])->name('webhooks.store');
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
    Route::get('/fee-breakdowns', [FeeBreakdownController::class, 'index'])->name('fee-breakdowns.index');
    Route::post('/fee-breakdowns', [FeeBreakdownController::class, 'store'])->name('fee-breakdowns.store');
    Route::get('/fee-breakdowns/export', [FeeBreakdownController::class, 'export'])->name('fee-breakdowns.export');
    Route::get('/admin/fee-structures', [FeeStructureController::class, 'index'])->name('admin.fee-structures');
    Route::post('/admin/fee-structures', [FeeStructureController::class, 'store'])->name('admin.fee-structures.store');
    Route::put('/admin/fee-structures/{feeStructure}', [FeeStructureController::class, 'update'])->name('admin.fee-structures.update');
    Route::get('/wallet-transfers', [WalletTransferController::class, 'index'])->name('wallet-transfers.index');
    Route::post('/wallet-transfers', [WalletTransferController::class, 'store'])->name('wallet-transfers.store');
    Route::get('/wallet-transfers/receive-qr-code', [WalletTransferController::class, 'receiveQrCode'])->name('wallet-transfers.receive-qr-code');
    Route::get('/admin/audit-logs', [AuditLogController::class, 'index'])->name('admin.audit-logs');
    Route::get('/admin/wallet-monitoring', [WalletMonitoringController::class, 'index'])->name('admin.wallet-monitoring');
    Route::get('/admin/businesses', [BusinessManagementController::class, 'index'])->name('admin.businesses.index');
    Route::get('/support-tickets', [SupportTicketController::class, 'index'])->name('support-tickets.index');
    Route::post('/support-tickets', [SupportTicketController::class, 'store'])->name('support-tickets.store');
    Route::get('/support-tickets/{ticket}', [SupportTicketController::class, 'show'])->name('support-tickets.show');
    Route::post('/support-tickets/{ticket}/messages', [SupportTicketMessageController::class, 'store'])->name('support-tickets.messages.store');
    Route::post('/support-tickets/{ticket}/resolve', [SupportTicketReviewController::class, 'resolve'])->name('support-tickets.resolve');
    Route::post('/support-tickets/{ticket}/reopen', [SupportTicketReviewController::class, 'reopen'])->name('support-tickets.reopen');
    Route::get('/knowledge-base', [KnowledgeBaseController::class, 'index'])->name('knowledge-base.index');
    Route::post('/knowledge-base', [KnowledgeBaseController::class, 'store'])->name('knowledge-base.store');
    Route::get('/knowledge-base/{article}', [KnowledgeBaseController::class, 'show'])->name('knowledge-base.show');
    Route::put('/knowledge-base/{article}', [KnowledgeBaseController::class, 'update'])->name('knowledge-base.update');
    Route::get('/announcements', [AnnouncementController::class, 'index'])->name('announcements.index');
    Route::post('/announcements', [AnnouncementController::class, 'store'])->name('announcements.store');
    Route::put('/announcements/{announcement}', [AnnouncementController::class, 'update'])->name('announcements.update');
    Route::get('/admin/landing-page', [LandingPageContentController::class, 'edit'])->name('admin.landing-page.edit');
    Route::put('/admin/landing-page', [LandingPageContentController::class, 'update'])->name('admin.landing-page.update');
    Route::get('/admin/legal-pages', [LegalPageController::class, 'index'])->name('admin.legal-pages');
    Route::put('/admin/legal-pages/{legalPage}', [LegalPageController::class, 'update'])->name('admin.legal-pages.update');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
