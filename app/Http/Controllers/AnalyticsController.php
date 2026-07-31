<?php

namespace App\Http\Controllers;

use App\Models\PaymentTransaction;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', PaymentTransaction::class);

        // PaymentTransaction is tenant-scoped, so these figures are already limited
        // to the current user's business for anyone who isn't a super admin.
        $totalTransactions = PaymentTransaction::count();
        $totalVolume = PaymentTransaction::sum('amount');
        $completedTransactions = PaymentTransaction::where('status', 'completed')->count();
        $processingTransactions = PaymentTransaction::where('status', 'processing')->count();

        return view('analytics.index', compact('totalTransactions', 'totalVolume', 'completedTransactions', 'processingTransactions'));
    }
}
