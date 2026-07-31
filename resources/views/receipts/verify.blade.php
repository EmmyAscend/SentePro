<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Receipt {{ $receipt->reference_number }} | SentePro</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-950 text-white">
    <div class="mx-auto flex min-h-screen max-w-lg items-center justify-center px-4 py-12">
        <div class="w-full rounded-3xl bg-slate-900 p-8 text-center shadow-2xl ring-1 ring-slate-700">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-emerald-500/20 text-emerald-400">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <h1 class="mt-4 text-xl font-bold">This receipt is genuine</h1>
            <p class="mt-2 text-sm text-slate-400">Reference {{ $receipt->reference_number }} was issued by SentePro.</p>

            <div class="mt-6 space-y-2 rounded-2xl bg-slate-800 p-5 text-sm ring-1 ring-slate-700">
                <div class="flex items-center justify-between">
                    <span class="text-slate-400">Business</span>
                    <span class="font-semibold">{{ $receipt->business->business_name }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-400">Amount</span>
                    <span class="font-semibold">{{ $receipt->currency }} {{ number_format($receipt->amount, 2) }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-400">Date</span>
                    <span class="font-semibold">{{ $receipt->created_at->format('d M Y') }}</span>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
