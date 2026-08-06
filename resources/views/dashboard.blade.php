<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-white">{{ __('SentePro Dashboard') }}</h2>
                <p class="text-sm text-slate-400">
                    @if (auth()->user()->isSuperAdmin())
                        Platform-wide operations overview
                    @else
                        Operations overview for {{ auth()->user()->business?->business_name ?? 'your business' }}
                    @endif
                </p>
            </div>
            @if (auth()->user()->isSuperAdmin())
                <a href="{{ route('business.register') }}" class="rounded-full bg-lime-400 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-lime-300">New business</a>
            @endif
        </div>
    </x-slot>

    @php
        $trendPoints = $transactionTrend->values();
        $maxCount = max(1, $trendPoints->max('count'));
        $chartWidth = 700;
        $chartHeight = 160;
        $padX = 12;
        $padY = 16;
        $stepX = $trendPoints->count() > 1 ? ($chartWidth - 2 * $padX) / ($trendPoints->count() - 1) : 0;
        $coords = $trendPoints->map(function ($point, $i) use ($stepX, $padX, $chartHeight, $padY, $maxCount) {
            return [
                'x' => round($padX + $i * $stepX, 1),
                'y' => round($chartHeight - $padY - ($point['count'] / $maxCount) * ($chartHeight - 2 * $padY), 1),
                'date' => $point['date'],
                'count' => $point['count'],
            ];
        })->values();
        $linePath = $coords->map(fn ($c, $i) => ($i === 0 ? 'M' : 'L') . $c['x'] . ' ' . $c['y'])->implode(' ');
        $baseline = $chartHeight - $padY;
        $areaPath = $linePath . " L {$coords->last()['x']} {$baseline} L {$coords->first()['x']} {$baseline} Z";

        // Customers/businesses never see "Pesapal"/"Yo Payments" anywhere
        // else in the app (see PublicCheckoutController::METHOD_PROVIDERS) —
        // a business admin's own dashboard keeps that same abstraction. Only
        // the super admin, who actually manages the gateway credentials
        // behind each label, sees the real provider names.
        $providerLabels = auth()->user()->isSuperAdmin()
            ? ['pesapal' => 'Pesapal', 'yo_payments' => 'Yo Payments']
            : ['pesapal' => 'Bank Cards', 'yo_payments' => 'Mobile Money'];
        $providerColors = ['pesapal' => 'bg-lime-600', 'yo_payments' => 'bg-sky-700'];
        $maxProvider = max(1, $providerSplit->max() ?? 0);

        $businessBadgeClasses = [
            'approved' => 'bg-emerald-500/15 text-emerald-300',
            'pending' => 'bg-amber-500/15 text-amber-300',
            'under_review' => 'bg-amber-500/15 text-amber-300',
            'rejected' => 'bg-rose-500/15 text-rose-300',
            'suspended' => 'bg-slate-500/15 text-slate-300',
        ];
    @endphp

    <div class="py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-4 md:grid-cols-4">
                <div class="rounded-2xl bg-slate-900 p-6 ring-1 ring-white/10">
                    <p class="text-sm text-slate-400">Registered Businesses</p>
                    <p class="mt-2 text-4xl font-bold text-white">{{ $businessCount }}</p>
                </div>
                <div class="rounded-2xl bg-slate-900 p-6 ring-1 ring-white/10">
                    <p class="text-sm text-slate-400">Wallet Balance</p>
                    @if ($currencyBreakdown->isEmpty())
                        <p class="mt-2 text-2xl font-bold text-white">No completed transactions yet</p>
                    @else
                        <div class="mt-2 space-y-0.5">
                            @foreach ($currencyBreakdown as $currency => $total)
                                <p class="text-2xl font-bold text-white">{{ $currency }} {{ number_format($total, 2) }}</p>
                            @endforeach
                        </div>
                    @endif
                </div>
                <div class="rounded-2xl bg-slate-900 p-6 ring-1 ring-white/10">
                    <p class="text-sm text-slate-400">Pending Settlements</p>
                    <p class="mt-2 text-4xl font-bold text-white">{{ $pendingSettlementsCount }}</p>
                </div>
                <div class="rounded-2xl bg-slate-900 p-6 ring-1 ring-white/10">
                    <p class="text-sm text-slate-400">Transactions Today</p>
                    <p class="mt-2 text-4xl font-bold text-white">{{ $transactionsToday }}</p>
                </div>
            </div>

            <div class="mt-6 grid gap-6 lg:grid-cols-[1.4fr_1fr]">
                <div class="rounded-2xl bg-slate-900 p-6 ring-1 ring-white/10" x-data="{ hovered: null }">
                    <h3 class="text-lg font-semibold text-white">Transaction volume</h3>
                    <p class="text-sm text-slate-400">Payment attempts across all connected gateways, last 14 days</p>
                    <div class="relative mt-4">
                        <svg viewBox="0 0 {{ $chartWidth }} {{ $chartHeight }}" class="h-40 w-full" preserveAspectRatio="none">
                            <line x1="{{ $padX }}" y1="{{ $baseline }}" x2="{{ $chartWidth - $padX }}" y2="{{ $baseline }}" class="stroke-white/10" stroke-width="1" />
                            <path d="{{ $areaPath }}" class="fill-lime-600/10" />
                            <path d="{{ $linePath }}" fill="none" class="stroke-lime-600" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            @foreach ($coords as $i => $c)
                                <circle cx="{{ $c['x'] }}" cy="{{ $c['y'] }}" r="4" class="fill-lime-600 stroke-slate-900" stroke-width="2" />
                                <circle cx="{{ $c['x'] }}" cy="{{ $c['y'] }}" r="12" class="cursor-pointer fill-transparent"
                                        @mouseenter="hovered = {{ $i }}" @mouseleave="hovered = null" />
                            @endforeach
                        </svg>
                        @foreach ($coords as $i => $c)
                            <div x-show="hovered === {{ $i }}"
                                 class="pointer-events-none absolute -translate-x-1/2 -translate-y-full whitespace-nowrap rounded-lg bg-slate-800 px-2.5 py-1.5 text-xs shadow-lg ring-1 ring-white/10"
                                 style="left: {{ ($c['x'] / $chartWidth) * 100 }}%; top: {{ max(0, ($c['y'] / $chartHeight) * 100 - 4) }}%; display: none;">
                                <span class="font-semibold text-white">{{ $c['count'] }}</span>
                                <span class="text-slate-400">· {{ \Illuminate\Support\Carbon::parse($c['date'])->format('M j') }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="rounded-2xl bg-slate-900 p-6 ring-1 ring-white/10">
                    <h3 class="text-lg font-semibold text-white">Provider split</h3>
                    <p class="text-sm text-slate-400">Share of payment attempts, last 14 days</p>
                    <div class="mt-6 space-y-4">
                        @foreach ($providerLabels as $key => $label)
                            @php $count = $providerSplit[$key] ?? 0; @endphp
                            <div>
                                <div class="flex items-center justify-between text-sm">
                                    <span class="flex items-center gap-2 font-medium text-slate-200">
                                        <span class="h-2.5 w-2.5 rounded-full {{ $providerColors[$key] }}"></span>
                                        {{ $label }}
                                    </span>
                                    <span class="text-slate-400">{{ $count }}</span>
                                </div>
                                <div class="mt-1.5 h-2 rounded-full bg-white/5">
                                    <div class="h-2 rounded-full {{ $providerColors[$key] }}" style="width: {{ $count > 0 ? max(4, ($count / $maxProvider) * 100) : 0 }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="mt-6 grid gap-6 lg:grid-cols-[1.15fr_0.85fr]">
                <div class="rounded-2xl bg-slate-900 p-6 ring-1 ring-white/10">
                    <div class="mb-4 flex items-center justify-between gap-4">
                        <h3 class="text-lg font-semibold text-white">Recent business onboarding</h3>
                        <div class="flex items-center gap-2">
                            @if (auth()->user()->isSuperAdmin())
                                <a href="{{ route('admin.businesses.index') }}" class="rounded-full border border-white/10 px-4 py-2 text-sm font-semibold text-slate-300 hover:bg-white/5">View all businesses</a>
                            @endif
                            <a href="{{ route('settlements.index') }}" class="rounded-full bg-lime-400 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-lime-300">Manage settlements</a>
                        </div>
                    </div>
                    @if ($latestBusinesses->isEmpty())
                        <p class="text-slate-400">No business applications recorded yet.</p>
                    @else
                        <div class="space-y-3">
                            @foreach ($latestBusinesses as $business)
                                <div class="rounded-xl bg-slate-800/60 px-4 py-3">
                                    <div class="flex items-center justify-between gap-3">
                                        <div>
                                            <div class="font-semibold text-white">{{ $business->business_name }}</div>
                                            <div class="text-sm text-slate-400">{{ $business->country }} • {{ $business->industry }}</div>
                                        </div>
                                        <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $businessBadgeClasses[$business->status] ?? 'bg-slate-500/15 text-slate-300' }}">
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
                                                <button type="submit" class="rounded-full bg-rose-500/15 px-3 py-1 text-xs font-semibold text-rose-300 hover:bg-rose-500/25">Reject</button>
                                            </form>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="rounded-2xl bg-slate-900 p-6 ring-1 ring-white/10">
                    <div class="mb-4 flex items-center justify-between gap-4">
                        <h3 class="text-lg font-semibold text-white">Live payment activity</h3>
                        <a href="{{ route('transactions.index') }}" class="rounded-full bg-emerald-500 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-emerald-400">View ledger</a>
                    </div>
                    @if ($latestTransactions->isEmpty())
                        <p class="text-slate-400">No payment activity recorded yet.</p>
                    @else
                        <div class="space-y-3">
                            @foreach ($latestTransactions as $transaction)
                                <div class="rounded-xl bg-slate-800/60 px-4 py-3">
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
                                        ])>{{ $transaction->status->label() }}</span>
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
