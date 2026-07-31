<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRefundRequest;
use App\Models\PaymentTransaction;
use App\Services\RefundService;
use Illuminate\Http\RedirectResponse;

class RefundController extends Controller
{
    public function __construct(private readonly RefundService $refundService) {}

    public function store(StoreRefundRequest $request, PaymentTransaction $transaction): RedirectResponse
    {
        $this->refundService->refund($transaction, $request->user(), $request->validated()['reason'] ?? null);

        return redirect()->route('transactions.index')->with('status', 'Refund processed successfully.');
    }
}
