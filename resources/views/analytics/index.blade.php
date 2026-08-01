<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-white">Analytics</h2>
                <p class="text-sm text-slate-400">Operational performance overview for collection activity</p>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-4 md:grid-cols-4">
                <div class="rounded-2xl bg-slate-900 p-6 text-white shadow-lg shadow-slate-950/20">
                    <p class="text-sm text-slate-300">Total transactions</p>
                    <p class="mt-2 text-4xl font-bold">{{ $totalTransactions }}</p>
                </div>
                <div class="rounded-2xl bg-slate-900 p-6 shadow-sm ring-1 ring-white/10">
                    <p class="text-sm text-slate-400">Collection volume</p>
                    <p class="mt-2 text-4xl font-bold text-white">{{ number_format($totalVolume, 2) }}</p>
                </div>
                <div class="rounded-2xl bg-slate-900 p-6 shadow-sm ring-1 ring-white/10">
                    <p class="text-sm text-slate-400">Completed</p>
                    <p class="mt-2 text-4xl font-bold text-white">{{ $completedTransactions }}</p>
                </div>
                <div class="rounded-2xl bg-slate-900 p-6 shadow-sm ring-1 ring-white/10">
                    <p class="text-sm text-slate-400">Processing</p>
                    <p class="mt-2 text-4xl font-bold text-white">{{ $processingTransactions }}</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
