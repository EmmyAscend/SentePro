<?php

namespace App\Http\Controllers;

use App\Enums\PaymentTransactionStatus;
use App\Enums\SettlementStatus;
use App\Models\Business;
use App\Models\PaymentTransaction;
use App\Models\Settlement;
use App\Models\Wallet;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        if ($user->isSuperAdmin()) {
            $businessCount = Business::count();
            $latestBusinesses = Business::latest()->take(5)->get();
        } else {
            $businessCount = $user->business_id ? 1 : 0;
            $latestBusinesses = Business::query()->whereKey($user->business_id)->get();
        }

        // PaymentTransaction, Wallet, and Settlement are all tenant-scoped, so
        // these are already limited to the current user's business for anyone
        // who isn't a super admin (the scope no-ops for super admins).
        $latestTransactions = PaymentTransaction::with('business')->latest()->take(5)->get();

        $walletBalance = (float) Wallet::sum('available_balance');

        // A live reporting view over completed transactions, not the wallet
        // ledger itself — Wallet stays a single flat balance with no
        // currency column. Tenant-scoped automatically for non-super-admins,
        // same as every other PaymentTransaction query on this dashboard.
        $currencyBreakdown = PaymentTransaction::query()
            ->where('status', PaymentTransactionStatus::Completed)
            ->selectRaw('currency, SUM(amount) as total')
            ->groupBy('currency')
            ->pluck('total', 'currency');

        $pendingSettlementsCount = Settlement::query()
            ->whereIn('status', [SettlementStatus::Pending, SettlementStatus::Processing])
            ->count();

        $transactionsToday = PaymentTransaction::query()->whereDate('created_at', today())->count();

        $trendStart = Carbon::today()->subDays(13);

        $dailyCounts = PaymentTransaction::query()
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->where('created_at', '>=', $trendStart)
            ->groupBy('day')
            ->pluck('total', 'day');

        $transactionTrend = collect(range(0, 13))->map(function (int $offset) use ($trendStart, $dailyCounts) {
            $day = $trendStart->copy()->addDays($offset)->toDateString();

            return ['date' => $day, 'count' => (int) ($dailyCounts[$day] ?? 0)];
        });

        $providerSplit = PaymentTransaction::query()
            ->where('created_at', '>=', $trendStart)
            ->selectRaw('provider, COUNT(*) as total')
            ->groupBy('provider')
            ->pluck('total', 'provider');

        return view('dashboard', compact(
            'businessCount',
            'latestBusinesses',
            'latestTransactions',
            'walletBalance',
            'currencyBreakdown',
            'pendingSettlementsCount',
            'transactionsToday',
            'transactionTrend',
            'providerSplit',
        ));
    }
}
