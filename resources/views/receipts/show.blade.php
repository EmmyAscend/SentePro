<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt {{ $receipt->reference_number }} | SentePro</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: #ffffff !important; }
        }
    </style>
</head>
<body class="bg-slate-950 text-white">
    <div class="mx-auto flex min-h-screen max-w-2xl items-center justify-center px-4 py-12">
        <div class="w-full rounded-3xl bg-slate-900 p-8 shadow-2xl ring-1 ring-slate-700 print:bg-white print:text-slate-900 print:shadow-none print:ring-0">
            <div class="mb-6 flex items-center justify-between no-print">
                <p class="text-sm uppercase tracking-[0.3em] text-emerald-400">SentePro Receipt</p>
                <button type="button" onclick="window.print()" class="rounded-full bg-emerald-500 px-4 py-2 text-xs font-semibold text-slate-950 hover:bg-emerald-400">
                    Print / Save as PDF
                </button>
            </div>

            <h1 class="text-2xl font-bold">{{ $receipt->business->business_name }}</h1>
            <p class="mt-1 text-sm text-slate-400 print:text-slate-500">Payment receipt</p>

            <div class="mt-6 space-y-3 rounded-2xl bg-slate-800 p-5 ring-1 ring-slate-700 print:bg-white print:ring-slate-200">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-slate-400 print:text-slate-500">Reference Number</span>
                    <span class="font-semibold">{{ $receipt->reference_number }}</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-slate-400 print:text-slate-500">Date</span>
                    <span class="font-semibold">{{ $receipt->created_at->format('d M Y, H:i') }}</span>
                </div>
                @if ($receipt->customer_name)
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-slate-400 print:text-slate-500">Paid by</span>
                        <span class="font-semibold">{{ $receipt->customer_name }}</span>
                    </div>
                @endif
                <div class="flex items-center justify-between border-t border-slate-700 pt-3 text-lg print:border-slate-200">
                    <span class="text-slate-400 print:text-slate-500">Amount Paid</span>
                    <span class="font-black">{{ $receipt->currency }} {{ number_format($receipt->amount, 2) }}</span>
                </div>
            </div>

            <div class="mt-6 flex items-center gap-4 rounded-2xl bg-slate-800 p-5 text-sm ring-1 ring-slate-700 print:bg-white print:ring-slate-200">
                <img src="{{ route('receipts.qr-code', $receipt) }}" alt="Scan to verify this receipt" width="96" height="96" class="h-24 w-24 shrink-0 rounded-lg bg-white p-1">
                <div class="min-w-0">
                    <p class="text-slate-400 print:text-slate-500">Scan or visit to verify this receipt</p>
                    <a href="{{ route('receipts.verify', $receipt) }}" class="mt-1 block break-all font-mono text-xs text-emerald-400 print:text-emerald-700">
                        {{ route('receipts.verify', $receipt) }}
                    </a>
                </div>
            </div>

            <p class="mt-6 text-center text-xs text-slate-500">Issued by SentePro on behalf of {{ $receipt->business->business_name }}.</p>
        </div>
    </div>
</body>
</html>
