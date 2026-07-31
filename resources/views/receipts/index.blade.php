<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-slate-900">Receipts</h2>
            <p class="text-sm text-slate-500">Receipts are generated automatically whenever a payment completes</p>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                <h3 class="text-lg font-semibold text-slate-900">Receipt history</h3>
                <div class="mt-4 space-y-3">
                    @forelse ($receipts as $receipt)
                        <a href="{{ route('receipts.show', $receipt) }}" target="_blank" class="block rounded-xl bg-slate-50 p-4 hover:bg-slate-100">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="font-semibold text-slate-900">{{ $receipt->reference_number }}</p>
                                    <p class="text-sm text-slate-500">
                                        {{ $receipt->business->business_name }}
                                        @if ($receipt->customer_name)
                                            • {{ $receipt->customer_name }}
                                        @endif
                                        • {{ $receipt->created_at->format('d M Y, H:i') }}
                                    </p>
                                </div>
                                <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">
                                    {{ $receipt->currency }} {{ number_format($receipt->amount, 2) }}
                                </span>
                            </div>
                        </a>
                    @empty
                        <p class="text-slate-600">No receipts yet — one is created automatically the first time a payment completes.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
