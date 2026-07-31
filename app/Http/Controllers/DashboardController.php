<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\PaymentTransaction;
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

        // PaymentTransaction is tenant-scoped, so this is already limited to the
        // current user's business for anyone who isn't a super admin.
        $latestTransactions = PaymentTransaction::with('business')->latest()->take(5)->get();

        return view('dashboard', compact('businessCount', 'latestBusinesses', 'latestTransactions'));
    }
}
