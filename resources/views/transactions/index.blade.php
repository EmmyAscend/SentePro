<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-900">Payment Transactions</h2>
                <p class="text-sm text-slate-500">Track the payment collection lifecycle for connected providers</p>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-6 lg:grid-cols-[1fr_1fr]">
                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                    <h3 class="text-lg font-semibold text-slate-900">Add transaction</h3>
                    <form method="POST" action="{{ route('transactions.store') }}" class="mt-4 space-y-4">
                        @csrf

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Business</label>
                            <select name="business_id" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2">
                                @foreach ($transactions as $transaction)
                                    <option value="{{ $transaction->business_id }}">{{ $transaction->business->business_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Provider</label>
                            <input type="text" name="provider" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Amount</label>
                            <input type="number" name="amount" step="0.01" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Currency</label>
                            <input type="text" name="currency" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Status</label>
                            <select name="status" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2">
                                <option value="processing">Processing</option>
                                <option value="completed">Completed</option>
                                <option value="failed">Failed</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">External reference</label>
                            <input type="text" name="external_reference" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2" required>
                        </div>

                        <button type="submit" class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Record transaction</button>
                    </form>
                </div>

                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                    <h3 class="text-lg font-semibold text-slate-900">Transaction ledger</h3>
                    <div class="mt-4 space-y-3">
                        @forelse ($transactions as $transaction)
                            <div class="rounded-xl bg-slate-50 p-4">
                                <div class="flex items-center justify-between gap-3">
                                    <div>
                                        <p class="font-semibold text-slate-900">{{ $transaction->business->business_name }}</p>
                                        <p class="text-sm text-slate-500">{{ $transaction->provider->label() }} • {{ $transaction->external_reference }}</p>
                                    </div>
                                    <span @class([
                                        'rounded-full px-3 py-1 text-xs font-semibold',
                                        'bg-emerald-100 text-emerald-700' => $transaction->status === \App\Enums\PaymentTransactionStatus::Completed,
                                        'bg-amber-100 text-amber-700' => $transaction->status === \App\Enums\PaymentTransactionStatus::Processing,
                                        'bg-rose-100 text-rose-700' => $transaction->status === \App\Enums\PaymentTransactionStatus::Failed,
                                        'bg-slate-200 text-slate-600' => $transaction->status === \App\Enums\PaymentTransactionStatus::Refunded,
                                    ])>{{ strtoupper($transaction->status->value) }}</span>
                                </div>
                                @if ($transaction->status === \App\Enums\PaymentTransactionStatus::Completed && $transaction->provider !== \App\Enums\PaymentProvider::YoPayments)
                                @can('refund', $transaction)
                                    <form method="POST" action="{{ route('transactions.refund', $transaction) }}" class="mt-3 flex items-center gap-2" onsubmit="return confirm('Refund this transaction? This cannot be undone.');">
                                        @csrf
                                        <input type="text" name="reason" placeholder="Refund reason (optional)" class="flex-1 rounded-xl border border-slate-300 px-3 py-1.5 text-sm">
                                        <button type="submit" class="rounded-full bg-rose-100 px-3 py-1 text-xs font-semibold text-rose-700 hover:bg-rose-200">Refund</button>
                                    </form>
                                @endcan
                                @endif
                            </div>
                        @empty
                            <p class="text-slate-600">No transactions recorded yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
