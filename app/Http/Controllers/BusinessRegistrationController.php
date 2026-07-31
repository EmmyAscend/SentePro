<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBusinessRequest;
use App\Services\BusinessRegistrationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BusinessRegistrationController extends Controller
{
    public function __construct(private readonly BusinessRegistrationService $registrationService) {}

    public function index(): View
    {
        return view('business.register');
    }

    public function store(StoreBusinessRequest $request): RedirectResponse
    {
        $owner = $this->registrationService->register($request->validated());

        Auth::login($owner);

        return redirect()->route('dashboard')
            ->with('status', 'Business registration submitted. Your account is pending verification.');
    }
}
