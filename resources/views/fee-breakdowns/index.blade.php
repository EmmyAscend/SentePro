<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-900">Fee Breakdowns</h2>
                <p class="text-sm text-slate-500">Break down gateway fees, platform markup, and net payout amounts</p>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-6 lg:grid-cols-[1fr_1fr]">
                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                    <h3 class="text-lg font-semibold text-slate-900">Create fee breakdown</h3>
                    <form method="POST" action="{{ route('fee-breakdowns.store') }}" class="mt-4 space-y-4">
                        @csrf

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Business</label>
                            <select name="business_id" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2">
                                @foreach ($feeBreakdowns as $breakdown)
                                    <option value="{{ $breakdown->business_id }}">{{ $breakdown->business->business_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Transaction reference</label>
                            <input type="text" name="transaction_reference" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Gateway fee</label>
                            <input type="number" step="0.01" name="gateway_fee" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Platform fee</label>
                            <input type="number" step="0.01" name="platform_fee" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Net amount</label>
                            <input type="number" step="0.01" name="net_amount" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2" required>
                        </div>

                        <button type="submit" class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Save breakdown</button>
                    </form>
                </div>

                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                    <h3 class="text-lg font-semibold text-slate-900">Recorded breakdowns</h3>
                    <div class="mt-4 space-y-3">
                        @forelse ($feeBreakdowns as $breakdown)
                            <div class="rounded-xl bg-slate-50 p-4">
                                <div class="flex items-center justify-between gap-3">
                                    <div>
                                        <p class="font-semibold text-slate-900">{{ $breakdown->transaction_reference }}</p>
                                        <p class="text-sm text-slate-500">{{ $breakdown->business->business_name }}</p>
                                    </div>
                                    <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">Net {{ number_format($breakdown->net_amount, 2) }}</span>
                                </div>
                            </div>
                        @empty
                            <p class="text-slate-600">No fee breakdowns created yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
