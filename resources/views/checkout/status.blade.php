<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Status | SentePro</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-950 text-white">
    <div class="mx-auto flex min-h-screen max-w-4xl items-center justify-center px-4 py-12">
        <div class="w-full rounded-3xl bg-slate-900 p-8 shadow-2xl ring-1 ring-slate-700">
            <p class="text-sm uppercase tracking-[0.3em] text-emerald-400">SentePro Receipt</p>
            <h1 class="mt-3 text-3xl font-bold text-white">Payment request received</h1>
            <p class="mt-2 text-slate-300">Your payment intent for {{ $paymentLink->title }} has been captured and is now being processed.</p>

            <div class="mt-6 grid gap-4 md:grid-cols-2">
                <div class="rounded-2xl bg-slate-800 p-5 ring-1 ring-slate-700">
                    <p class="text-sm text-slate-400">Business</p>
                    <p class="mt-2 text-lg font-semibold text-white">{{ $paymentLink->business->business_name }}</p>
                    <p class="mt-3 text-sm text-slate-300">Amount: {{ number_format($transaction?->amount ?? $paymentLink->amount, 2) }}</p>
                    <p class="mt-2 text-sm text-slate-300">Status: {{ $transaction?->status?->label() ?? 'Processing' }}</p>
                    @if ($transaction?->provider === \App\Enums\PaymentProvider::YoPayments && $transaction->status === \App\Enums\PaymentTransactionStatus::Processing)
                        <p class="mt-2 text-xs text-emerald-400">Check your phone to approve the mobile money prompt.</p>
                    @endif
                </div>

                <div class="rounded-2xl bg-emerald-500 p-5 text-slate-950">
                    <p class="text-sm font-semibold">Reference</p>
                    <p class="mt-2 text-lg font-bold">{{ $transaction?->external_reference ?? 'Awaiting provider response' }}</p>
                    <p class="mt-3 text-sm">A business admin can follow this transaction from their dashboard ledger.</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
