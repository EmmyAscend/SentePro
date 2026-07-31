<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStaffRequest;
use App\Models\Business;
use App\Models\User;
use App\Services\StaffService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class StaffManagementController extends Controller
{
    public function __construct(private readonly StaffService $staffService) {}

    public function index(): View
    {
        $this->authorize('viewAny', User::class);

        $user = auth()->user();

        $businesses = $user->isSuperAdmin()
            ? Business::query()->latest()->get()
            : Business::query()->whereKey($user->business_id)->get();

        $staff = User::query()
            ->whereIn('role', ['business_admin', 'staff'])
            ->when(! $user->isSuperAdmin(), fn ($query) => $query->where('business_id', $user->business_id))
            ->latest()
            ->get();

        return view('admin.staff', compact('businesses', 'staff'));
    }

    public function store(StoreStaffRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $business = $request->user()->isSuperAdmin()
            ? Business::findOrFail($validated['business_id'])
            : Business::findOrFail($request->user()->business_id);

        $this->staffService->create($business, $validated);

        return redirect()->route('admin.staff')->with('status', 'Staff member created successfully.');
    }
}
