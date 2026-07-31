<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-slate-900">Transaction Fee Structures</h2>
            <p class="text-sm text-slate-500">Configure incoming payment fees per gateway, with optional per-business overrides</p>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                <h3 class="text-lg font-semibold text-slate-900">Add fee structure</h3>
                <form method="POST" action="{{ route('admin.fee-structures.store') }}" class="mt-4 grid gap-4 md:grid-cols-3">
                    @csrf
                    <label class="flex flex-col gap-1 text-sm">
                        <span class="font-medium text-slate-700">Provider</span>
                        <select name="provider" class="rounded-xl border border-slate-300 px-3 py-2">
                            <option value="pesapal">Pesapal</option>
                            <option value="yo_payments">Yo Payments</option>
                        </select>
                    </label>
                    <label class="flex flex-col gap-1 text-sm">
                        <span class="font-medium text-slate-700">Business (blank = platform default)</span>
                        <select name="business_id" class="rounded-xl border border-slate-300 px-3 py-2">
                            <option value="">Platform default</option>
                            @foreach ($businesses as $business)
                                <option value="{{ $business->id }}">{{ $business->business_name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="flex flex-col gap-1 text-sm">
                        <span class="font-medium text-slate-700">Status</span>
                        <select name="status" class="rounded-xl border border-slate-300 px-3 py-2">
                            <option value="enabled">Enabled</option>
                            <option value="disabled">Disabled</option>
                        </select>
                    </label>
                    <label class="flex flex-col gap-1 text-sm">
                        <span class="font-medium text-slate-700">Gateway Fee %</span>
                        <input type="number" step="0.01" min="0" max="100" name="gateway_fee_percent" value="0" class="rounded-xl border border-slate-300 px-3 py-2" required>
                    </label>
                    <label class="flex flex-col gap-1 text-sm">
                        <span class="font-medium text-slate-700">Gateway Fee (flat)</span>
                        <input type="number" step="0.01" min="0" name="gateway_fee_flat" value="0" class="rounded-xl border border-slate-300 px-3 py-2" required>
                    </label>
                    <label class="flex flex-col gap-1 text-sm">
                        <span class="font-medium text-slate-700">Platform Fee %</span>
                        <input type="number" step="0.01" min="0" max="100" name="platform_fee_percent" value="0" class="rounded-xl border border-slate-300 px-3 py-2" required>
                    </label>
                    <label class="flex flex-col gap-1 text-sm">
                        <span class="font-medium text-slate-700">Platform Fee (flat)</span>
                        <input type="number" step="0.01" min="0" name="platform_fee_flat" value="0" class="rounded-xl border border-slate-300 px-3 py-2" required>
                    </label>
                    <label class="flex flex-col gap-1 text-sm">
                        <span class="font-medium text-slate-700">Minimum Fee</span>
                        <input type="number" step="0.01" min="0" name="min_fee" class="rounded-xl border border-slate-300 px-3 py-2">
                    </label>
                    <label class="flex flex-col gap-1 text-sm">
                        <span class="font-medium text-slate-700">Maximum Fee</span>
                        <input type="number" step="0.01" min="0" name="max_fee" class="rounded-xl border border-slate-300 px-3 py-2">
                    </label>
                    <div class="md:col-span-3">
                        <button type="submit" class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Add fee structure</button>
                    </div>
                </form>
            </div>

            <div class="space-y-4">
                @forelse ($feeStructures as $structure)
                    <div x-data="{ editing: false }" class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p class="font-semibold text-slate-900">
                                    {{ $structure->provider->label() }}
                                    <span class="text-slate-400">({{ $structure->business?->business_name ?? 'Platform default' }})</span>
                                </p>
                                <p class="text-sm text-slate-500">
                                    Gateway {{ $structure->gateway_fee_percent }}% + {{ number_format($structure->gateway_fee_flat, 2) }} •
                                    Platform {{ $structure->platform_fee_percent }}% + {{ number_format($structure->platform_fee_flat, 2) }}
                                    @if ($structure->min_fee !== null || $structure->max_fee !== null)
                                        • Clamped {{ $structure->min_fee !== null ? 'min '.number_format($structure->min_fee, 2) : '' }}{{ $structure->min_fee !== null && $structure->max_fee !== null ? ' / ' : '' }}{{ $structure->max_fee !== null ? 'max '.number_format($structure->max_fee, 2) : '' }}
                                    @endif
                                </p>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="rounded-full {{ $structure->status === 'enabled' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }} px-3 py-1 text-xs font-semibold">
                                    {{ strtoupper($structure->status) }}
                                </span>
                                <button type="button" @click="editing = !editing" class="rounded-full border border-slate-300 px-3 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                                    Edit
                                </button>
                            </div>
                        </div>

                        <form x-show="editing" x-cloak method="POST" action="{{ route('admin.fee-structures.update', $structure) }}" class="mt-4 grid gap-4 border-t border-slate-100 pt-4 md:grid-cols-3">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="provider" value="{{ $structure->provider->value }}">
                            <input type="hidden" name="business_id" value="{{ $structure->business_id }}">
                            <label class="flex flex-col gap-1 text-sm">
                                <span class="font-medium text-slate-700">Status</span>
                                <select name="status" class="rounded-xl border border-slate-300 px-3 py-2">
                                    <option value="enabled" @selected($structure->status === 'enabled')>Enabled</option>
                                    <option value="disabled" @selected($structure->status === 'disabled')>Disabled</option>
                                </select>
                            </label>
                            <label class="flex flex-col gap-1 text-sm">
                                <span class="font-medium text-slate-700">Gateway Fee %</span>
                                <input type="number" step="0.01" min="0" max="100" name="gateway_fee_percent" value="{{ $structure->gateway_fee_percent }}" class="rounded-xl border border-slate-300 px-3 py-2" required>
                            </label>
                            <label class="flex flex-col gap-1 text-sm">
                                <span class="font-medium text-slate-700">Gateway Fee (flat)</span>
                                <input type="number" step="0.01" min="0" name="gateway_fee_flat" value="{{ $structure->gateway_fee_flat }}" class="rounded-xl border border-slate-300 px-3 py-2" required>
                            </label>
                            <label class="flex flex-col gap-1 text-sm">
                                <span class="font-medium text-slate-700">Platform Fee %</span>
                                <input type="number" step="0.01" min="0" max="100" name="platform_fee_percent" value="{{ $structure->platform_fee_percent }}" class="rounded-xl border border-slate-300 px-3 py-2" required>
                            </label>
                            <label class="flex flex-col gap-1 text-sm">
                                <span class="font-medium text-slate-700">Platform Fee (flat)</span>
                                <input type="number" step="0.01" min="0" name="platform_fee_flat" value="{{ $structure->platform_fee_flat }}" class="rounded-xl border border-slate-300 px-3 py-2" required>
                            </label>
                            <label class="flex flex-col gap-1 text-sm">
                                <span class="font-medium text-slate-700">Minimum Fee</span>
                                <input type="number" step="0.01" min="0" name="min_fee" value="{{ $structure->min_fee }}" class="rounded-xl border border-slate-300 px-3 py-2">
                            </label>
                            <label class="flex flex-col gap-1 text-sm">
                                <span class="font-medium text-slate-700">Maximum Fee</span>
                                <input type="number" step="0.01" min="0" name="max_fee" value="{{ $structure->max_fee }}" class="rounded-xl border border-slate-300 px-3 py-2">
                            </label>
                            <div class="md:col-span-3">
                                <button type="submit" class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Save changes</button>
                            </div>
                        </form>
                    </div>
                @empty
                    <p class="text-slate-600">No fee structures configured yet — transactions will be credited with zero fees until one is added.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
