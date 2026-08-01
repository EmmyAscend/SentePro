<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-white">Payment Transactions</h2>
                <p class="text-sm text-slate-400">Track the payment collection lifecycle for connected providers</p>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-6 lg:grid-cols-[1fr_1fr]">
                <div class="rounded-2xl bg-slate-900 p-6 shadow-sm ring-1 ring-white/10">
                    <h3 class="text-lg font-semibold text-white">Add transaction</h3>
                    <form method="POST" action="{{ route('transactions.store') }}" class="mt-4 space-y-4">
                        @csrf

                        <div>
                            <label class="block text-sm font-medium text-slate-300">Business</label>
                            <select name="business_id" class="mt-1 w-full rounded-xl border border-white/10 bg-slate-950 px-3 py-2 text-white">
                                @foreach ($businesses as $business)
                                    <option value="{{ $business->id }}">{{ $business->business_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-300">Provider</label>
                            <input type="text" name="provider" class="mt-1 w-full rounded-xl border border-white/10 bg-slate-950 px-3 py-2 text-white" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-300">Amount</label>
                            <input type="number" name="amount" step="0.01" class="mt-1 w-full rounded-xl border border-white/10 bg-slate-950 px-3 py-2 text-white" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-300">Currency</label>
                            <input type="text" name="currency" class="mt-1 w-full rounded-xl border border-white/10 bg-slate-950 px-3 py-2 text-white" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-300">Status</label>
                            <select name="status" class="mt-1 w-full rounded-xl border border-white/10 bg-slate-950 px-3 py-2 text-white">
                                <option value="processing">Processing</option>
                                <option value="completed">Completed</option>
                                <option value="failed">Failed</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-300">External reference</label>
                            <input type="text" name="external_reference" class="mt-1 w-full rounded-xl border border-white/10 bg-slate-950 px-3 py-2 text-white" required>
                        </div>

                        <button type="submit" class="rounded-xl bg-lime-400 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-lime-300">Record transaction</button>
                    </form>
                </div>

                <div class="rounded-2xl bg-slate-900 p-6 shadow-sm ring-1 ring-white/10">
                    <div class="flex items-center justify-between gap-4">
                        <h3 class="text-lg font-semibold text-white">Transaction ledger</h3>
                        <a href="{{ route('transactions.export', request()->query()) }}" class="rounded-full border border-white/10 px-3 py-1.5 text-xs font-semibold text-slate-300 hover:bg-white/5">Export CSV</a>
                    </div>

                    <form method="GET" action="{{ route('transactions.index') }}" class="mt-4 grid gap-3 rounded-xl bg-slate-800/60 p-4 md:grid-cols-3">
                        <label class="flex flex-col gap-1 text-sm md:col-span-3">
                            <span class="font-medium text-slate-300">Search</span>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Reference, customer name, email, or phone" class="rounded-xl border border-white/10 bg-slate-950 px-3 py-2 text-white">
                        </label>
                        <label class="flex flex-col gap-1 text-sm">
                            <span class="font-medium text-slate-300">Status</span>
                            <select name="status" class="rounded-xl border border-white/10 bg-slate-950 px-3 py-2 text-white">
                                <option value="">All statuses</option>
                                @foreach (['processing', 'completed', 'failed', 'refunded', 'partially_refunded'] as $status)
                                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucwords(str_replace('_', ' ', $status)) }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="flex flex-col gap-1 text-sm">
                            <span class="font-medium text-slate-300">Provider</span>
                            <select name="provider" class="rounded-xl border border-white/10 bg-slate-950 px-3 py-2 text-white">
                                <option value="">All providers</option>
                                <option value="pesapal" @selected(request('provider') === 'pesapal')>Pesapal</option>
                                <option value="yo_payments" @selected(request('provider') === 'yo_payments')>Yo Payments</option>
                            </select>
                        </label>
                        @if (auth()->user()->isSuperAdmin())
                            <label class="flex flex-col gap-1 text-sm">
                                <span class="font-medium text-slate-300">Business</span>
                                <select name="business_id" class="rounded-xl border border-white/10 bg-slate-950 px-3 py-2 text-white">
                                    <option value="">All businesses</option>
                                    @foreach ($businesses as $business)
                                        <option value="{{ $business->id }}" @selected(request('business_id') == $business->id)>{{ $business->business_name }}</option>
                                    @endforeach
                                </select>
                            </label>
                        @endif
                        <label class="flex flex-col gap-1 text-sm">
                            <span class="font-medium text-slate-300">From</span>
                            <input type="date" name="date_from" value="{{ request('date_from') }}" class="rounded-xl border border-white/10 bg-slate-950 px-3 py-2 text-white">
                        </label>
                        <label class="flex flex-col gap-1 text-sm">
                            <span class="font-medium text-slate-300">To</span>
                            <input type="date" name="date_to" value="{{ request('date_to') }}" class="rounded-xl border border-white/10 bg-slate-950 px-3 py-2 text-white">
                        </label>
                        <div class="flex items-end gap-2">
                            <button type="submit" class="rounded-xl bg-lime-400 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-lime-300">Filter</button>
                            <a href="{{ route('transactions.index') }}" class="rounded-xl border border-white/10 px-4 py-2 text-sm font-semibold text-slate-300 hover:bg-white/5">Clear</a>
                        </div>
                    </form>

                    <div class="mt-4 space-y-3">
                        @forelse ($transactions as $transaction)
                            <div class="rounded-xl bg-slate-800/60 p-4">
                                <div class="flex items-center justify-between gap-3">
                                    <div>
                                        <p class="font-semibold text-white">{{ $transaction->business->business_name }}</p>
                                        <p class="text-sm text-slate-400">{{ $transaction->provider->label() }} • {{ $transaction->external_reference }}</p>
                                    </div>
                                    <span @class([
                                        'rounded-full px-3 py-1 text-xs font-semibold',
                                        'bg-emerald-500/15 text-emerald-300' => $transaction->status === \App\Enums\PaymentTransactionStatus::Completed,
                                        'bg-amber-500/15 text-amber-300' => $transaction->status === \App\Enums\PaymentTransactionStatus::Processing,
                                        'bg-rose-500/15 text-rose-300' => $transaction->status === \App\Enums\PaymentTransactionStatus::Failed,
                                        'bg-slate-500/15 text-slate-300' => $transaction->status === \App\Enums\PaymentTransactionStatus::Refunded,
                                        'bg-orange-500/15 text-orange-300' => $transaction->status === \App\Enums\PaymentTransactionStatus::PartiallyRefunded,
                                    ])>{{ strtoupper($transaction->status->value) }}</span>
                                </div>
                                @if (! empty($transaction->custom_field_values))
                                    <p class="mt-1 text-xs text-slate-400">
                                        {{ collect($transaction->custom_field_values)->map(fn ($field) => $field['label'].': '.$field['value'])->implode(' • ') }}
                                    </p>
                                @endif
                                @if (in_array($transaction->status, [\App\Enums\PaymentTransactionStatus::Completed, \App\Enums\PaymentTransactionStatus::PartiallyRefunded], true) && $transaction->provider !== \App\Enums\PaymentProvider::YoPayments)
                                @can('refund', $transaction)
                                    <form method="POST" action="{{ route('transactions.refund', $transaction) }}" class="mt-3 flex items-center gap-2" onsubmit="return confirm('Refund this transaction? This cannot be undone.');">
                                        @csrf
                                        <input type="number" name="amount" step="0.01" min="0.01" max="{{ $transaction->remainingRefundableAmount() }}" value="{{ $transaction->remainingRefundableAmount() }}" class="w-28 rounded-xl border border-white/10 bg-slate-950 px-3 py-1.5 text-sm text-white" title="Up to {{ number_format($transaction->remainingRefundableAmount(), 2) }} remaining">
                                        <input type="text" name="reason" placeholder="Refund reason (optional)" class="flex-1 rounded-xl border border-white/10 bg-slate-950 px-3 py-1.5 text-sm text-white">
                                        <button type="submit" class="rounded-full bg-rose-500/15 px-3 py-1 text-xs font-semibold text-rose-300 hover:bg-rose-500/25">Refund</button>
                                    </form>
                                @endcan
                                @endif
                                @if (in_array($transaction->status, [\App\Enums\PaymentTransactionStatus::Completed, \App\Enums\PaymentTransactionStatus::PartiallyRefunded, \App\Enums\PaymentTransactionStatus::Refunded], true))
                                @can('create', [\App\Models\Dispute::class, $transaction])
                                    <form method="POST" action="{{ route('disputes.store', $transaction) }}" class="mt-2 flex items-center gap-2">
                                        @csrf
                                        <input type="text" name="reason" placeholder="Dispute reason" class="flex-1 rounded-xl border border-white/10 bg-slate-950 px-3 py-1.5 text-sm text-white" required>
                                        <button type="submit" class="rounded-full bg-amber-500/15 px-3 py-1 text-xs font-semibold text-amber-300 hover:bg-amber-500/25">Dispute</button>
                                    </form>
                                @endcan
                                @endif
                            </div>
                        @empty
                            <p class="text-slate-400">No transactions match these filters.</p>
                        @endforelse
                    </div>

                    <div class="mt-4">
                        {{ $transactions->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
