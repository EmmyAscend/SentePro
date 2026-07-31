<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReviewBusinessRequest;
use App\Models\Business;
use App\Services\BusinessVerificationService;
use Illuminate\Http\RedirectResponse;

class BusinessReviewController extends Controller
{
    public function __construct(private readonly BusinessVerificationService $verificationService) {}

    public function review(ReviewBusinessRequest $request, Business $business): RedirectResponse
    {
        $validated = $request->validated();

        $this->verificationService->review(
            $business,
            $request->user(),
            $validated['status'],
            $validated['review_notes'] ?? null,
        );

        return redirect()->route('dashboard')->with('status', 'Business review updated.');
    }
}
