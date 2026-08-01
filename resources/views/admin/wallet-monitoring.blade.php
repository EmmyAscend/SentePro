<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-slate-900">Wallet Monitoring</h2>
            <p class="text-sm text-slate-500">Platform-wide float and per-business wallet balances</p>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="grid gap-4 md:grid-cols-5">
                <div class="rounded-2xl bg-slate-900 p-6 text-white shadow-lg shadow-slate-950/20">
                    <p class="text-sm text-slate-300">Total Platform Float</p>
                    <p class="mt-2 text-3xl font-bold">
                        {{ number_format($platformTotals['available_balance'] + $platformTotals['pending_balance'] + $platformTotals['reserved_balance'] + $platformTotals['settlement_balance'], 2) }}
                    </p>
                </div>
                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                    <p class="text-sm text-slate-500">Available Balance</p>
                    <p class="mt-2 text-3xl font-bold text-slate-900">{{ number_format($platformTotals['available_balance'], 2) }}</p>
                </div>
                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                    <p class="text-sm text-slate-500">Pending Settlements</p>
                    <p class="mt-2 text-3xl font-bold text-slate-900">{{ number_format($pendingSettlementsAmount, 2) }}</p>
                    <p class="text-xs text-slate-400">{{ $pendingSettlementsCount }} request{{ $pendingSettlementsCount === 1 ? '' : 's' }}</p>
                </div>
                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                    <p class="text-sm text-slate-500">Completed Settlements</p>
                    <p class="mt-2 text-3xl font-bold text-slate-900">{{ number_format($completedSettlementsAmount, 2) }}</p>
                    <p class="text-xs text-slate-400">{{ $completedSettlementsCount }} paid out</p>
                </div>
                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                    <p class="text-sm text-slate-500">Wallet Transfers</p>
                    <p class="mt-2 text-3xl font-bold text-slate-900">{{ number_format($walletTransfersAmount, 2) }}</p>
                    <p class="text-xs text-slate-400">{{ $walletTransfersCount }} transfer{{ $walletTransfersCount === 1 ? '' : 's' }}</p>
                </div>
            </div>

            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                <h3 class="text-lg font-semibold text-slate-900">Wallets by business</h3>
                @if ($businesses->isEmpty())
                    <p class="mt-4 text-slate-600">No businesses registered yet.</p>
                @else
                    <div class="mt-4 space-y-3">
                        @foreach ($businesses as $business)
                            <div class="rounded-xl bg-slate-50 px-4 py-3">
                                <div class="flex flex-wrap items-center justify-between gap-3">
                                    <div class="font-semibold text-slate-900">{{ $business->business_name }}</div>
                                    <div class="flex flex-wrap gap-x-6 gap-y-1 text-sm text-slate-600">
                                        <span>Available <strong class="text-slate-900">{{ number_format($business->wallet?->available_balance ?? 0, 2) }}</strong></span>
                                        <span>Pending <strong class="text-slate-900">{{ number_format($business->wallet?->pending_balance ?? 0, 2) }}</strong></span>
                                        <span>Reserved <strong class="text-slate-900">{{ number_format($business->wallet?->reserved_balance ?? 0, 2) }}</strong></span>
                                        <span>Settlement <strong class="text-slate-900">{{ number_format($business->wallet?->settlement_balance ?? 0, 2) }}</strong></span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
