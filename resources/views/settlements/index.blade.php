<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-900">Settlements</h2>
                <p class="text-sm text-slate-500">Raise a settlement request from your business wallet</p>
            </div>
            @if (auth()->user()->isSuperAdmin())
                <a href="{{ route('admin.settlement-methods') }}" class="rounded-full bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Settlement methods</a>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="grid gap-6 lg:grid-cols-[1.15fr_0.85fr]">
                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                    <h3 class="text-lg font-semibold text-slate-900">Wallet snapshot</h3>
                    <div class="mt-4 space-y-3">
                        @foreach ($businesses as $business)
                            <div class="rounded-xl bg-slate-50 p-4">
                                <div class="flex items-center justify-between gap-3">
                                    <div>
                                        <p class="font-semibold text-slate-900">{{ $business->business_name }}</p>
                                        <p class="text-sm text-slate-500">Available balance: {{ number_format($business->wallet?->available_balance ?? 0, 2) }}</p>
                                    </div>
                                    <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">{{ strtoupper($business->status) }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div
                    class="rounded-2xl bg-slate-900 p-6 text-white shadow-lg shadow-slate-950/20"
                    x-data="{
                        methods: {{ \Illuminate\Support\Js::from($settlementMethods->keyBy('id')->map(fn ($method) => [
                            'settlement_fee_percent' => (float) $method->settlement_fee_percent,
                            'settlement_fee_flat' => (float) $method->settlement_fee_flat,
                            'platform_fee_percent' => (float) $method->platform_fee_percent,
                            'platform_fee_flat' => (float) $method->platform_fee_flat,
                            'min_amount' => $method->min_amount !== null ? (float) $method->min_amount : null,
                            'max_amount' => $method->max_amount !== null ? (float) $method->max_amount : null,
                            'processing_time' => $method->processing_time,
                            'time_unit' => str_replace('_', ' ', $method->time_unit->value),
                        ])) }},
                        methodId: '',
                        amount: '',
                        get method() { return this.methods[this.methodId] ?? null },
                        get gatewayFee() { return this.method && this.amount ? (this.amount * this.method.settlement_fee_percent / 100 + this.method.settlement_fee_flat) : 0 },
                        get platformFee() { return this.method && this.amount ? (this.amount * this.method.platform_fee_percent / 100 + this.method.platform_fee_flat) : 0 },
                        get totalFee() { return this.gatewayFee + this.platformFee },
                        get netAmount() { return this.amount ? (this.amount - this.totalFee) : 0 },
                    }"
                >
                    <h3 class="text-lg font-semibold">Request settlement</h3>
                    <form method="POST" action="{{ route('settlements.store') }}" class="mt-4 space-y-4">
                        @csrf

                        @if (auth()->user()->isSuperAdmin())
                            <div>
                                <label class="block text-sm font-medium text-slate-200">Business</label>
                                <select name="business_id" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-800 px-3 py-2 text-sm text-white">
                                    @foreach ($businesses as $business)
                                        <option value="{{ $business->id }}">{{ $business->business_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div>
                            <label class="block text-sm font-medium text-slate-200">Amount</label>
                            <input type="number" min="1" step="0.01" name="amount" x-model="amount" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-800 px-3 py-2 text-sm text-white" placeholder="2500" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-200">Settlement method</label>
                            <select name="settlement_method_id" x-model="methodId" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-800 px-3 py-2 text-sm text-white" required>
                                <option value="" disabled selected>Choose a method</option>
                                @foreach ($settlementMethods as $method)
                                    <option value="{{ $method->id }}">{{ $method->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div x-show="method" x-cloak class="rounded-xl bg-slate-800 p-4 text-sm">
                            <p class="flex justify-between"><span>Gateway fee</span><span x-text="gatewayFee.toFixed(2)"></span></p>
                            <p class="flex justify-between"><span>Platform fee</span><span x-text="platformFee.toFixed(2)"></span></p>
                            <p class="mt-1 flex justify-between border-t border-slate-700 pt-1 font-semibold"><span>Net settlement</span><span x-text="netAmount.toFixed(2)"></span></p>
                            <p class="mt-2 text-slate-400" x-show="method" x-text="method ? ('Estimated processing time: within ' + method.processing_time + ' ' + method.time_unit) : ''"></p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-200">Notes</label>
                            <textarea name="notes" rows="4" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-800 px-3 py-2 text-sm text-white" placeholder="Optional settlement note"></textarea>
                        </div>

                        <button type="submit" class="w-full rounded-xl bg-emerald-500 px-4 py-3 text-sm font-semibold text-slate-950 hover:bg-emerald-400">Submit settlement request</button>
                    </form>
                </div>
            </div>

            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                <h3 class="text-lg font-semibold text-slate-900">Settlement queue</h3>
                <div class="mt-4 space-y-3">
                    @forelse ($settlements as $settlement)
                        <div class="rounded-xl bg-slate-50 p-4">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="font-semibold text-slate-900">{{ $settlement->business->business_name }} — {{ number_format($settlement->amount, 2) }}</p>
                                    <p class="text-sm text-slate-500">
                                        {{ $settlement->settlementMethod?->name ?? 'Unknown method' }} •
                                        Net {{ number_format($settlement->net_amount, 2) }} •
                                        Fees {{ number_format($settlement->gateway_fee + $settlement->platform_fee, 2) }}
                                        @if ($settlement->estimated_completion_at)
                                            • Est. {{ $settlement->estimated_completion_at->format('M j, Y H:i') }}
                                        @endif
                                    </p>
                                </div>
                                <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">{{ $settlement->status->label() }}</span>
                            </div>
                            @if (auth()->user()->isSuperAdmin() && in_array($settlement->status->value, ['pending', 'processing']))
                                <div class="mt-3 flex gap-2">
                                    <form method="POST" action="{{ route('admin.settlements.complete', $settlement) }}">
                                        @csrf
                                        <button type="submit" class="rounded-full bg-emerald-500 px-3 py-1 text-xs font-semibold text-slate-950 hover:bg-emerald-400">Complete</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.settlements.reject', $settlement) }}">
                                        @csrf
                                        <button type="submit" class="rounded-full bg-rose-100 px-3 py-1 text-xs font-semibold text-rose-700 hover:bg-rose-200">Reject</button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    @empty
                        <p class="text-slate-600">No settlement requests yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
