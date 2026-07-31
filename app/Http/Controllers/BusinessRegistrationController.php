<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class BusinessRegistrationController extends Controller
{
    public function index(): View
    {
        return view('business.register');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'business_name' => ['required', 'string', 'max:255'],
            'trading_name' => ['required', 'string', 'max:255'],
            'registration_number' => ['required', 'string', 'max:255'],
            'country' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'email' => ['required', 'email'],
            'industry' => ['required', 'string', 'max:255'],
            'expected_monthly_volume' => ['required', 'string', 'max:255'],
            'business_description' => ['nullable', 'string'],
        ]);

        return redirect()->route('business.register')->with('status', 'Business registration submitted.');
    }
}
