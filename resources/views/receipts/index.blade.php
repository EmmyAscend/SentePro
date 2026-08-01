<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-white">Receipts</h2>
            <p class="text-sm text-slate-400">Receipts are generated automatically whenever a payment completes</p>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-2xl bg-slate-900 p-6 shadow-sm ring-1 ring-white/10">
                <div class="flex items-center justify-between gap-4">
                    <h3 class="text-lg font-semibold text-white">Receipt history</h3>
                    <a href="{{ route('receipts.export') }}" class="rounded-full border border-white/10 px-3 py-1.5 text-xs font-semibold text-slate-300 hover:bg-white/5">Export CSV</a>
                </div>
                <div class="mt-4 space-y-3">
                    @forelse ($receipts as $receipt)
                        <a href="{{ route('receipts.show', $receipt) }}" target="_blank" class="block rounded-xl bg-slate-800/60 p-4 hover:bg-white/5">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="font-semibold text-white">{{ $receipt->reference_number }}</p>
                                    <p class="text-sm text-slate-400">
                                        {{ $receipt->business->business_name }}
                                        @if ($receipt->customer_name)
                                            • {{ $receipt->customer_name }}
                                        @endif
                                        • {{ $receipt->created_at->format('d M Y, H:i') }}
                                    </p>
                                </div>
                                <span class="rounded-full bg-emerald-500/15 px-3 py-1 text-xs font-semibold text-emerald-300">
                                    {{ $receipt->currency }} {{ number_format($receipt->amount, 2) }}
                                </span>
                            </div>
                        </a>
                    @empty
                        <p class="text-slate-400">No receipts yet — one is created automatically the first time a payment completes.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
