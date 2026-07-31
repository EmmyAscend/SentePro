<?php

namespace App\Http\Controllers;

use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BusinessManagementController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('manage', Business::class);

        $businesses = Business::query()
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->latest()
            ->get();

        return view('admin.businesses', compact('businesses'));
    }
}
