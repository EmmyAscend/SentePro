<?php

namespace App\Http\Controllers;

use App\Http\Requests\FeeStructureRequest;
use App\Models\Business;
use App\Models\FeeStructure;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FeeStructureController extends Controller
{
    public function index(): View
    {
        $this->authorize('manage', FeeStructure::class);

        $feeStructures = FeeStructure::with('business')->orderBy('provider')->orderBy('business_id')->get();
        $businesses = Business::query()->orderBy('business_name')->get();

        return view('admin.fee-structures', compact('feeStructures', 'businesses'));
    }

    public function store(FeeStructureRequest $request): RedirectResponse
    {
        FeeStructure::create($request->validated());

        return redirect()->route('admin.fee-structures')->with('status', 'Fee structure created.');
    }

    public function update(FeeStructureRequest $request, FeeStructure $feeStructure): RedirectResponse
    {
        $feeStructure->update($request->validated());

        return redirect()->route('admin.fee-structures')->with('status', 'Fee structure updated.');
    }
}
