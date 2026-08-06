<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Status | SentePro</title>
    <link href="https://fonts.bunny.net/css?family=syne:400,500,600,700,800|pacifico:400&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: #ffffff !important; }
        }
    </style>
</head>
<body class="font-sans bg-slate-950 text-white">
    @php
        $status = $transaction?->status;
        $isCompleted = $status === \App\Enums\PaymentTransactionStatus::Completed;
        $isFailed = $status === \App\Enums\PaymentTransactionStatus::Failed;
        $isRefunded = in_array($status, [\App\Enums\PaymentTransactionStatus::Refunded, \App\Enums\PaymentTransactionStatus::PartiallyRefunded], true);

        $heading = match (true) {
            $isCompleted => 'Payment successful',
            $isFailed => 'Payment failed',
            $isRefunded => 'Payment '.strtolower($status->label()),
            default => 'Payment request received',
        };
        $subtext = match (true) {
            $isCompleted => 'Your payment has been confirmed. Thank you!',
            $isFailed => 'This payment could not be completed. You can go back and try again.',
            $isRefunded => 'This payment has been '.strtolower($status->label()).'.',
            default => 'Your payment intent for '.$paymentLink->title.' has been captured and is now being processed.',
        };
    @endphp
    <div class="mx-auto flex min-h-screen max-w-4xl items-center justify-center px-4 py-12">
        <div class="w-full rounded-3xl bg-slate-900 p-8 shadow-2xl ring-1 ring-white/10 print:bg-white print:text-slate-900 print:shadow-none print:ring-0">
            <x-brand-mark class="text-3xl" />
            <p class="mt-4 text-sm uppercase tracking-[0.3em] text-lime-300">SentePro Receipt</p>

            <div class="mt-3 flex items-center gap-3">
                <div id="status-icon-completed" class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-lime-400/20 text-lime-300 {{ $isCompleted ? '' : 'hidden' }}">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <div id="status-icon-failed" class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-rose-400/20 text-rose-300 {{ $isFailed ? '' : 'hidden' }}">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </div>
                <h1 id="status-heading" class="text-3xl font-bold text-white">{{ $heading }}</h1>
            </div>
            <p id="status-subtext" class="mt-2 text-slate-300">{{ $subtext }}</p>

            @if ($isCompleted && $transaction->receipt)
                <div class="mt-6 flex items-center justify-between gap-2 no-print">
                    <button type="button" onclick="navigator.clipboard.writeText('{{ $transaction->external_reference }}'); this.textContent='Copied!'" class="flex shrink-0 items-center gap-1 rounded-full border border-white/10 px-3 py-1.5 text-xs font-semibold text-slate-300 hover:bg-white/5">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                        Copy reference
                    </button>
                    <button type="button" onclick="window.print()" class="rounded-full bg-lime-400 px-4 py-2 text-xs font-semibold text-slate-950 hover:bg-lime-300">
                        Print / Save as PDF
                    </button>
                </div>

                <x-receipt-card :receipt="$transaction->receipt" />

                <p class="mt-6 text-center text-xs text-slate-500 no-print">Tip: take a screenshot of this page to keep as proof of payment.</p>
            @else
                <div class="mt-6 grid gap-4 md:grid-cols-2">
                    <div class="rounded-2xl bg-slate-800 p-5 ring-1 ring-white/10">
                        <p class="text-sm text-slate-400">Business</p>
                        <p class="mt-2 text-lg font-semibold text-white">{{ $paymentLink->business->business_name }}</p>
                        <p class="mt-3 text-sm text-slate-300">Amount: {{ number_format($transaction?->amount ?? $paymentLink->amount, 2) }}</p>
                        <p id="status-label" class="mt-2 text-sm text-slate-300">Status: {{ $transaction?->status?->label() ?? 'Processing' }}</p>
                        @if ($transaction?->provider === \App\Enums\PaymentProvider::YoPayments && $transaction->status === \App\Enums\PaymentTransactionStatus::Processing)
                            <p id="mobile-money-hint" class="mt-2 text-xs text-lime-300">Check your phone to approve the mobile money prompt.</p>
                        @endif
                    </div>

                    <div class="rounded-2xl bg-lime-400 p-5 text-slate-950">
                        <div class="flex items-center justify-between gap-2">
                            <p class="text-sm font-semibold">Reference</p>
                            @if ($transaction)
                                <button type="button" onclick="navigator.clipboard.writeText('{{ $transaction->external_reference }}'); this.textContent='Copied!'" class="flex shrink-0 items-center gap-1 rounded-full bg-slate-950/10 px-2.5 py-1 text-xs font-semibold hover:bg-slate-950/20">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                    Copy
                                </button>
                            @endif
                        </div>
                        <p id="reference-value" class="mt-2 text-lg font-extrabold">{{ $transaction?->external_reference ?? 'Awaiting provider response' }}</p>
                        <p id="status-footnote" class="mt-3 text-sm">A business admin can follow this transaction from their dashboard ledger.</p>
                    </div>
                </div>

                <p class="mt-6 text-center text-xs text-slate-500">Tip: take a screenshot of this page to keep as proof of payment.</p>
            @endif
        </div>
    </div>

    @if ($transaction)
        <script>
            (function () {
                var message = {
                    source: 'sentepro-checkout',
                    status: {{ \Illuminate\Support\Js::from($transaction->status->value) }},
                    reference: {{ \Illuminate\Support\Js::from($transaction->external_reference) }},
                };

                if (window.opener) {
                    window.opener.postMessage(message, '*');
                }
                if (window.parent && window.parent !== window) {
                    window.parent.postMessage(message, '*');
                }
            })();

            @if ($transaction->status === \App\Enums\PaymentTransactionStatus::Processing)
                (function () {
                    var checkUrl = {{ \Illuminate\Support\Js::from(route('checkout.status.check', $paymentLink)) }};
                    var attempts = 0;
                    var maxAttempts = 30;
                    var intervalMs = 4000;

                    var subtext = document.getElementById('status-subtext');

                    function stopPolling() {
                        clearInterval(timer);
                    }

                    function poll() {
                        attempts += 1;

                        fetch(checkUrl, { headers: { Accept: 'application/json' } })
                            .then(function (response) { return response.json(); })
                            .then(function (data) {
                                if (data.status === 'processing') {
                                    if (attempts >= maxAttempts) {
                                        stopPolling();
                                        subtext.textContent = 'Still processing — this is taking longer than usual. Refresh this page to check again.';
                                    }
                                    return;
                                }

                                // Reload rather than patch the DOM in place — the server
                                // already knows how to render every terminal state
                                // (including the inline receipt card once completed),
                                // so there's no need to duplicate that logic in JS.
                                stopPolling();
                                window.location.reload();
                            })
                            .catch(function () {
                                // A transient network error shouldn't stop the poll loop —
                                // just wait for the next tick.
                            });
                    }

                    var timer = setInterval(poll, intervalMs);
                    poll();
                })();
            @endif
        </script>
    @endif
</body>
</html>
