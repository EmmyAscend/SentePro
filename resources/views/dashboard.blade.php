<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-900">{{ __('SentePro Dashboard') }}</h2>
                <p class="text-sm text-slate-500">
                    @if (auth()->user()->isSuperAdmin())
                        Platform-wide operations overview
                    @else
                        Operations overview for {{ auth()->user()->business?->business_name ?? 'your business' }}
                    @endif
                </p>
            </div>
            @if (auth()->user()->isSuperAdmin())
                <a href="{{ route('business.register') }}" class="rounded-full bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">New business</a>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-4 md:grid-cols-4">
                <div class="rounded-2xl bg-slate-900 p-6 text-white shadow-lg shadow-slate-950/20">
                    <p class="text-sm text-slate-300">Registered Businesses</p>
                    <p class="mt-2 text-4xl font-bold">{{ $businessCount }}</p>
                </div>
                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                    <p class="text-sm text-slate-500">Wallet Balance</p>
                    <p class="mt-2 text-4xl font-bold text-slate-900">$0.00</p>
                </div>
                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                    <p class="text-sm text-slate-500">Settlements</p>
                    <p class="mt-2 text-4xl font-bold text-slate-900">Pending</p>
                </div>
                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                    <p class="text-sm text-slate-500">Your Role</p>
                    <p class="mt-2 text-4xl font-bold text-slate-900">{{ auth()->user()->role->label() }}</p>
                </div>
            </div>

            <div class="mt-6 grid gap-6 lg:grid-cols-[1.15fr_0.85fr]">
                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                    <div class="mb-4 flex items-center justify-between gap-4">
                        <h3 class="text-lg font-semibold text-slate-900">Recent business onboarding</h3>
                        <a href="{{ route('settlements.index') }}" class="rounded-full bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Manage settlements</a>
                    </div>
                    @if ($latestBusinesses->isEmpty())
                        <p class="text-slate-600">No business applications recorded yet.</p>
                    @else
                        <div class="space-y-3">
                            @foreach ($latestBusinesses as $business)
                                <div class="rounded-xl bg-slate-50 px-4 py-3">
                                    <div class="flex items-center justify-between gap-3">
                                        <div>
                                            <div class="font-semibold text-slate-900">{{ $business->business_name }}</div>
                                            <div class="text-sm text-slate-500">{{ $business->country }} • {{ $business->industry }}</div>
                                        </div>
                                        <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">
                                            @if ($business->status === 'pending')
                                                Pending review
                                            @else
                                                {{ ucfirst($business->status) }}
                                            @endif
                                        </span>
                                    </div>
                                    @if (auth()->user()->isSuperAdmin() && in_array($business->status, ['pending', 'under_review']))
                                        <div class="mt-3 flex gap-2">
                                            <form method="POST" action="{{ route('admin.businesses.review', $business) }}">
                                                @csrf
                                                <input type="hidden" name="status" value="approved">
                                                <button type="submit" class="rounded-full bg-emerald-500 px-3 py-1 text-xs font-semibold text-slate-950 hover:bg-emerald-400">Approve</button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.businesses.review', $business) }}">
                                                @csrf
                                                <input type="hidden" name="status" value="rejected">
                                                <button type="submit" class="rounded-full bg-rose-100 px-3 py-1 text-xs font-semibold text-rose-700 hover:bg-rose-200">Reject</button>
                                            </form>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                    <div class="mb-4 flex items-center justify-between gap-4">
                        <h3 class="text-lg font-semibold text-slate-900">Live payment activity</h3>
                        <a href="{{ route('transactions.index') }}" class="rounded-full bg-emerald-500 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-emerald-400">View ledger</a>
                    </div>
                    @if ($latestTransactions->isEmpty())
                        <p class="text-slate-600">No payment activity recorded yet.</p>
                    @else
                        <div class="space-y-3">
                            @foreach ($latestTransactions as $transaction)
                                <div class="rounded-xl bg-slate-50 px-4 py-3">
                                    <div class="flex items-center justify-between gap-3">
                                        <div>
                                            <p class="font-semibold text-slate-900">{{ $transaction->business->business_name }}</p>
                                            <p class="text-sm text-slate-500">{{ $transaction->provider->label() }} • {{ $transaction->external_reference }}</p>
                                        </div>
                                        <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">{{ $transaction->status->label() }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
