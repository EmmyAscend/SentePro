<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePaymentTransactionRequest;
use App\Models\Business;
use App\Models\PaymentTransaction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PaymentTransactionController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', PaymentTransaction::class);

        $user = $request->user();

        $businesses = $user->isSuperAdmin()
            ? Business::query()->orderBy('business_name')->get()
            : Business::query()->whereKey($user->business_id)->get();

        $transactions = $this->filteredQuery($request)->latest()->paginate(25)->withQueryString();

        return view('transactions.index', compact('transactions', 'businesses'));
    }

    public function store(StorePaymentTransactionRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $businessId = $request->user()->isSuperAdmin()
            ? $validated['business_id']
            : $request->user()->business_id;

        PaymentTransaction::create([...$validated, 'business_id' => $businessId]);

        return redirect()->route('transactions.index')->with('status', 'Transaction recorded successfully.');
    }

    public function export(Request $request): StreamedResponse
    {
        $this->authorize('viewAny', PaymentTransaction::class);

        $transactions = $this->filteredQuery($request)->latest()->get();

        return response()->streamDownload(function () use ($transactions) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Business', 'Provider', 'Amount', 'Currency', 'Status',
                'External Reference', 'Provider Reference',
                'Customer Name', 'Customer Email', 'Customer Phone', 'Date',
            ]);

            foreach ($transactions as $transaction) {
                fputcsv($handle, [
                    $transaction->business->business_name,
                    $transaction->provider->label(),
                    $transaction->amount,
                    $transaction->currency,
                    $transaction->status->label(),
                    $transaction->external_reference,
                    $transaction->provider_reference,
                    $transaction->customer_name,
                    $transaction->customer_email,
                    $transaction->customer_phone,
                    $transaction->created_at->toDateTimeString(),
                ]);
            }

            fclose($handle);
        }, 'transactions-'.now()->format('Y-m-d-His').'.csv', ['Content-Type' => 'text/csv']);
    }

    private function filteredQuery(Request $request): Builder
    {
        $user = $request->user();

        return PaymentTransaction::with('business')
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('provider'), fn ($query) => $query->where('provider', $request->string('provider')))
            ->when($user->isSuperAdmin() && $request->filled('business_id'), fn ($query) => $query->where('business_id', $request->integer('business_id')))
            ->when($request->filled('date_from'), fn ($query) => $query->whereDate('created_at', '>=', $request->date('date_from')))
            ->when($request->filled('date_to'), fn ($query) => $query->whereDate('created_at', '<=', $request->date('date_to')))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');
                $query->where(function ($query) use ($search) {
                    $query->where('external_reference', 'like', "%{$search}%")
                        ->orWhere('provider_reference', 'like', "%{$search}%")
                        ->orWhere('customer_name', 'like', "%{$search}%")
                        ->orWhere('customer_email', 'like', "%{$search}%")
                        ->orWhere('customer_phone', 'like', "%{$search}%");
                });
            });
    }
}
