<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFeeBreakdownRequest;
use App\Models\Business;
use App\Models\FeeBreakdown;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FeeBreakdownController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', FeeBreakdown::class);

        $user = $request->user();

        $businesses = $user->isSuperAdmin()
            ? Business::query()->orderBy('business_name')->get()
            : Business::query()->whereKey($user->business_id)->get();

        $feeBreakdowns = FeeBreakdown::with('business')->latest()->get();

        return view('fee-breakdowns.index', compact('feeBreakdowns', 'businesses'));
    }

    public function store(StoreFeeBreakdownRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $businessId = $request->user()->isSuperAdmin()
            ? $validated['business_id']
            : $request->user()->business_id;

        FeeBreakdown::create([...$validated, 'business_id' => $businessId]);

        return redirect()->route('fee-breakdowns.index')->with('status', 'Fee breakdown recorded successfully.');
    }

    public function export(): StreamedResponse
    {
        $this->authorize('viewAny', FeeBreakdown::class);

        $feeBreakdowns = FeeBreakdown::with('business')->latest()->get();

        return response()->streamDownload(function () use ($feeBreakdowns) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Business', 'Transaction Reference', 'Gateway Fee', 'Platform Fee', 'Net Amount', 'Date']);

            foreach ($feeBreakdowns as $breakdown) {
                fputcsv($handle, [
                    $breakdown->business->business_name,
                    $breakdown->transaction_reference,
                    $breakdown->gateway_fee,
                    $breakdown->platform_fee,
                    $breakdown->net_amount,
                    $breakdown->created_at->toDateTimeString(),
                ]);
            }

            fclose($handle);
        }, 'fee-breakdowns-'.now()->format('Y-m-d-His').'.csv', ['Content-Type' => 'text/csv']);
    }
}
