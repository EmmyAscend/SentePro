<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Status | SentePro</title>
    <link href="https://fonts.bunny.net/css?family=syne:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans bg-slate-950 text-white">
    <div class="mx-auto flex min-h-screen max-w-4xl items-center justify-center px-4 py-12">
        <div class="w-full rounded-3xl bg-slate-900 p-8 shadow-2xl ring-1 ring-white/10">
            <p class="text-sm uppercase tracking-[0.3em] text-lime-300">SentePro Receipt</p>
            <h1 id="status-heading" class="mt-3 text-3xl font-bold text-white">Payment request received</h1>
            <p id="status-subtext" class="mt-2 text-slate-300">Your payment intent for {{ $paymentLink->title }} has been captured and is now being processed.</p>

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
                    <p class="text-sm font-semibold">Reference</p>
                    <p class="mt-2 text-lg font-extrabold">{{ $transaction?->external_reference ?? 'Awaiting provider response' }}</p>
                    <p id="status-footnote" class="mt-3 text-sm">A business admin can follow this transaction from their dashboard ledger.</p>
                    <a id="receipt-link" href="#" class="mt-3 hidden w-full items-center justify-center rounded-xl bg-slate-950 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-900">View receipt</a>
                </div>
            </div>
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

                    var heading = document.getElementById('status-heading');
                    var subtext = document.getElementById('status-subtext');
                    var statusLabel = document.getElementById('status-label');
                    var footnote = document.getElementById('status-footnote');
                    var receiptLink = document.getElementById('receipt-link');
                    var mobileMoneyHint = document.getElementById('mobile-money-hint');

                    var labels = {
                        completed: 'Completed',
                        failed: 'Failed',
                        refunded: 'Refunded',
                        partially_refunded: 'Partially Refunded',
                    };

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

                                stopPolling();
                                statusLabel.textContent = 'Status: ' + (labels[data.status] || data.status);
                                if (mobileMoneyHint) {
                                    mobileMoneyHint.remove();
                                }

                                if (data.status === 'completed') {
                                    heading.textContent = 'Payment successful';
                                    subtext.textContent = 'Your payment has been confirmed.';
                                    if (data.receipt_url) {
                                        footnote.textContent = 'Your receipt is ready.';
                                        receiptLink.href = data.receipt_url;
                                        receiptLink.classList.remove('hidden');
                                        receiptLink.classList.add('flex');
                                        window.location.href = data.receipt_url;
                                    }
                                } else if (data.status === 'failed') {
                                    heading.textContent = 'Payment failed';
                                    subtext.textContent = 'This payment could not be completed. You can go back and try again.';
                                } else {
                                    heading.textContent = 'Payment ' + (labels[data.status] || data.status).toLowerCase();
                                }
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
