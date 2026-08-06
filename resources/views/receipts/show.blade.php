<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt {{ $receipt->reference_number }} | SentePro</title>
    <link href="https://fonts.bunny.net/css?family=syne:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: #ffffff !important; }
        }
    </style>
</head>
<body class="font-sans bg-slate-950 text-white">
    <div class="mx-auto flex min-h-screen max-w-2xl items-center justify-center px-4 py-12">
        <div class="w-full rounded-3xl bg-slate-900 p-8 shadow-2xl ring-1 ring-white/10 print:bg-white print:text-slate-900 print:shadow-none print:ring-0">
            <div class="mb-6 flex items-center justify-between no-print">
                <p class="text-sm uppercase tracking-[0.3em] text-lime-300">SentePro Receipt</p>
                <button type="button" onclick="window.print()" class="rounded-full bg-lime-400 px-4 py-2 text-xs font-semibold text-slate-950 hover:bg-lime-300">
                    Print / Save as PDF
                </button>
            </div>

            <h1 class="text-2xl font-bold">{{ $receipt->business->business_name }}</h1>
            <p class="mt-1 text-sm text-slate-400 print:text-slate-500">Payment receipt</p>

            <x-receipt-card :receipt="$receipt" />

            <p class="mt-6 text-center text-xs text-slate-500">Issued by SentePro on behalf of {{ $receipt->business->business_name }}.</p>
        </div>
    </div>
</body>
</html>
