@props(['receipt'])

<div class="mt-6 space-y-3 rounded-2xl bg-slate-800 p-5 ring-1 ring-white/10 print:bg-white print:ring-slate-200">
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
        <span class="font-extrabold">{{ $receipt->currency }} {{ number_format($receipt->amount, 2) }}</span>
    </div>
</div>

<div class="mt-6 flex items-center gap-4 rounded-2xl bg-slate-800 p-5 text-sm ring-1 ring-white/10 print:bg-white print:ring-slate-200">
    <img src="{{ route('receipts.qr-code', $receipt) }}" alt="Scan to verify this receipt" width="96" height="96" class="h-24 w-24 shrink-0 rounded-lg bg-white p-1">
    <div class="min-w-0">
        <p class="text-slate-400 print:text-slate-500">Scan or visit to verify this receipt</p>
        <a href="{{ route('receipts.verify', $receipt) }}" class="mt-1 block break-all font-mono text-xs text-lime-300 print:text-lime-700">
            {{ route('receipts.verify', $receipt) }}
        </a>
    </div>
</div>
