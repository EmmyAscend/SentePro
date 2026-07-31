<?php

namespace App\Http\Controllers;

use App\Models\Business;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $businessCount = Business::count();
        $latestBusinesses = Business::latest()->take(5)->get();

        return view('dashboard', compact('businessCount', 'latestBusinesses'));
    }
}
