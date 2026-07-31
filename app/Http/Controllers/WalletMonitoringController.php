<?php

namespace App\Http\Controllers;

use App\Enums\SettlementStatus;
use App\Models\Business;
use App\Models\Settlement;
use App\Models\Wallet;
use Illuminate\View\View;

class WalletMonitoringController extends Controller
{
    public function index(): View
    {
        $this->authorize('manage', Wallet::class);

        // Business has no tenant scope, so this is already cross-tenant.
        $businesses = Business::query()->with('wallet')->orderBy('business_name')->get();

        $platformTotals = [
            'available_balance' => $businesses->sum(fn (Business $business) => (float) $business->wallet?->available_balance),
            'pending_balance' => $businesses->sum(fn (Business $business) => (float) $business->wallet?->pending_balance),
            'reserved_balance' => $businesses->sum(fn (Business $business) => (float) $business->wallet?->reserved_balance),
            'settlement_balance' => $businesses->sum(fn (Business $business) => (float) $business->wallet?->settlement_balance),
        ];

        $pendingSettlements = Settlement::query()
            ->whereIn('status', [SettlementStatus::Pending, SettlementStatus::Processing]);
        $pendingSettlementsCount = (clone $pendingSettlements)->count();
        $pendingSettlementsAmount = (clone $pendingSettlements)->sum('amount');

        $completedSettlements = Settlement::query()->where('status', SettlementStatus::Completed);
        $completedSettlementsCount = (clone $completedSettlements)->count();
        $completedSettlementsAmount = (clone $completedSettlements)->sum('net_amount');

        return view('admin.wallet-monitoring', [
            'businesses' => $businesses,
            'platformTotals' => $platformTotals,
            'pendingSettlementsCount' => $pendingSettlementsCount,
            'pendingSettlementsAmount' => $pendingSettlementsAmount,
            'completedSettlementsCount' => $completedSettlementsCount,
            'completedSettlementsAmount' => $completedSettlementsAmount,
        ]);
    }
}
