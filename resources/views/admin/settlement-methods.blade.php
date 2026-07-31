<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-slate-900">Settlement Methods</h2>
            <p class="text-sm text-slate-500">Configure fees, processing times, and limits — no deployment required</p>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                <h3 class="text-lg font-semibold text-slate-900">Add settlement method</h3>
                <form method="POST" action="{{ route('admin.settlement-methods.store') }}" class="mt-4 grid gap-4 md:grid-cols-3">
                    @csrf
                    <label class="flex flex-col gap-1 text-sm">
                        <span class="font-medium text-slate-700">Code</span>
                        <input type="text" name="code" placeholder="mobile_money" class="rounded-xl border border-slate-300 px-3 py-2" required>
                    </label>
                    <label class="flex flex-col gap-1 text-sm">
                        <span class="font-medium text-slate-700">Name</span>
                        <input type="text" name="name" placeholder="Mobile Money" class="rounded-xl border border-slate-300 px-3 py-2" required>
                    </label>
                    <label class="flex flex-col gap-1 text-sm">
                        <span class="font-medium text-slate-700">Status</span>
                        <select name="status" class="rounded-xl border border-slate-300 px-3 py-2">
                            <option value="enabled">Enabled</option>
                            <option value="disabled">Disabled</option>
                        </select>
                    </label>
                    <label class="flex flex-col gap-1 text-sm">
                        <span class="font-medium text-slate-700">Processing Time</span>
                        <input type="number" min="0" name="processing_time" placeholder="24" class="rounded-xl border border-slate-300 px-3 py-2" required>
                    </label>
                    <label class="flex flex-col gap-1 text-sm">
                        <span class="font-medium text-slate-700">Time Unit</span>
                        <select name="time_unit" class="rounded-xl border border-slate-300 px-3 py-2">
                            <option value="hours">Hours</option>
                            <option value="days">Days</option>
                            <option value="working_days">Working Days</option>
                        </select>
                    </label>
                    <label class="flex flex-col gap-1 text-sm">
                        <span class="font-medium text-slate-700">Gateway Fee %</span>
                        <input type="number" step="0.01" min="0" max="100" name="settlement_fee_percent" value="0" class="rounded-xl border border-slate-300 px-3 py-2" required>
                    </label>
                    <label class="flex flex-col gap-1 text-sm">
                        <span class="font-medium text-slate-700">Gateway Fee (flat)</span>
                        <input type="number" step="0.01" min="0" name="settlement_fee_flat" value="0" class="rounded-xl border border-slate-300 px-3 py-2" required>
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
                        <span class="font-medium text-slate-700">Min Amount</span>
                        <input type="number" step="0.01" min="0" name="min_amount" class="rounded-xl border border-slate-300 px-3 py-2">
                    </label>
                    <label class="flex flex-col gap-1 text-sm">
                        <span class="font-medium text-slate-700">Max Amount</span>
                        <input type="number" step="0.01" min="0" name="max_amount" class="rounded-xl border border-slate-300 px-3 py-2">
                    </label>
                    <label class="flex flex-col gap-1 text-sm">
                        <span class="font-medium text-slate-700">Daily Limit</span>
                        <input type="number" step="0.01" min="0" name="daily_limit" class="rounded-xl border border-slate-300 px-3 py-2">
                    </label>
                    <label class="flex items-center gap-2 text-sm font-medium text-slate-700">
                        <input type="hidden" name="auto_approval" value="0">
                        <input type="checkbox" name="auto_approval" value="1" class="rounded border-slate-300">
                        Auto approval
                    </label>
                    <label class="flex items-center gap-2 text-sm font-medium text-slate-700">
                        <input type="hidden" name="weekend_processing" value="0">
                        <input type="checkbox" name="weekend_processing" value="1" class="rounded border-slate-300">
                        Weekend processing
                    </label>
                    <label class="flex flex-col gap-1 text-sm md:col-span-3">
                        <span class="font-medium text-slate-700">Public Description</span>
                        <input type="text" name="public_description" placeholder="Estimated settlement time: within 24 working hours." class="rounded-xl border border-slate-300 px-3 py-2">
                    </label>
                    <label class="flex flex-col gap-1 text-sm md:col-span-3">
                        <span class="font-medium text-slate-700">Internal Notes</span>
                        <textarea name="internal_notes" rows="2" class="rounded-xl border border-slate-300 px-3 py-2"></textarea>
                    </label>
                    <div class="md:col-span-3">
                        <button type="submit" class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Add method</button>
                    </div>
                </form>
            </div>

            <div class="space-y-4">
                @forelse ($settlementMethods as $method)
                    <div x-data="{ editing: false }" class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p class="font-semibold text-slate-900">{{ $method->name }} <span class="text-slate-400">({{ $method->code }})</span></p>
                                <p class="text-sm text-slate-500">
                                    {{ $method->processing_time }} {{ str_replace('_', ' ', $method->time_unit->value) }} •
                                    Gateway {{ $method->settlement_fee_percent }}% + {{ number_format($method->settlement_fee_flat, 2) }} •
                                    Platform {{ $method->platform_fee_percent }}% + {{ number_format($method->platform_fee_flat, 2) }}
                                </p>
                                @if ($method->public_description)
                                    <p class="mt-1 text-sm text-slate-600">{{ $method->public_description }}</p>
                                @endif
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="rounded-full {{ $method->status === 'enabled' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }} px-3 py-1 text-xs font-semibold">
                                    {{ strtoupper($method->status) }}
                                </span>
                                <button type="button" @click="editing = !editing" class="rounded-full border border-slate-300 px-3 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                                    Edit
                                </button>
                            </div>
                        </div>

                        <form x-show="editing" x-cloak method="POST" action="{{ route('admin.settlement-methods.update', $method) }}" class="mt-4 grid gap-4 border-t border-slate-100 pt-4 md:grid-cols-3">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="code" value="{{ $method->code }}">
                            <label class="flex flex-col gap-1 text-sm">
                                <span class="font-medium text-slate-700">Name</span>
                                <input type="text" name="name" value="{{ $method->name }}" class="rounded-xl border border-slate-300 px-3 py-2" required>
                            </label>
                            <label class="flex flex-col gap-1 text-sm">
                                <span class="font-medium text-slate-700">Status</span>
                                <select name="status" class="rounded-xl border border-slate-300 px-3 py-2">
                                    <option value="enabled" @selected($method->status === 'enabled')>Enabled</option>
                                    <option value="disabled" @selected($method->status === 'disabled')>Disabled</option>
                                </select>
                            </label>
                            <label class="flex flex-col gap-1 text-sm">
                                <span class="font-medium text-slate-700">Processing Time</span>
                                <input type="number" min="0" name="processing_time" value="{{ $method->processing_time }}" class="rounded-xl border border-slate-300 px-3 py-2" required>
                            </label>
                            <label class="flex flex-col gap-1 text-sm">
                                <span class="font-medium text-slate-700">Time Unit</span>
                                <select name="time_unit" class="rounded-xl border border-slate-300 px-3 py-2">
                                    <option value="hours" @selected($method->time_unit->value === 'hours')>Hours</option>
                                    <option value="days" @selected($method->time_unit->value === 'days')>Days</option>
                                    <option value="working_days" @selected($method->time_unit->value === 'working_days')>Working Days</option>
                                </select>
                            </label>
                            <label class="flex flex-col gap-1 text-sm">
                                <span class="font-medium text-slate-700">Gateway Fee %</span>
                                <input type="number" step="0.01" min="0" max="100" name="settlement_fee_percent" value="{{ $method->settlement_fee_percent }}" class="rounded-xl border border-slate-300 px-3 py-2" required>
                            </label>
                            <label class="flex flex-col gap-1 text-sm">
                                <span class="font-medium text-slate-700">Gateway Fee (flat)</span>
                                <input type="number" step="0.01" min="0" name="settlement_fee_flat" value="{{ $method->settlement_fee_flat }}" class="rounded-xl border border-slate-300 px-3 py-2" required>
                            </label>
                            <label class="flex flex-col gap-1 text-sm">
                                <span class="font-medium text-slate-700">Platform Fee %</span>
                                <input type="number" step="0.01" min="0" max="100" name="platform_fee_percent" value="{{ $method->platform_fee_percent }}" class="rounded-xl border border-slate-300 px-3 py-2" required>
                            </label>
                            <label class="flex flex-col gap-1 text-sm">
                                <span class="font-medium text-slate-700">Platform Fee (flat)</span>
                                <input type="number" step="0.01" min="0" name="platform_fee_flat" value="{{ $method->platform_fee_flat }}" class="rounded-xl border border-slate-300 px-3 py-2" required>
                            </label>
                            <label class="flex flex-col gap-1 text-sm">
                                <span class="font-medium text-slate-700">Min Amount</span>
                                <input type="number" step="0.01" min="0" name="min_amount" value="{{ $method->min_amount }}" class="rounded-xl border border-slate-300 px-3 py-2">
                            </label>
                            <label class="flex flex-col gap-1 text-sm">
                                <span class="font-medium text-slate-700">Max Amount</span>
                                <input type="number" step="0.01" min="0" name="max_amount" value="{{ $method->max_amount }}" class="rounded-xl border border-slate-300 px-3 py-2">
                            </label>
                            <label class="flex flex-col gap-1 text-sm">
                                <span class="font-medium text-slate-700">Daily Limit</span>
                                <input type="number" step="0.01" min="0" name="daily_limit" value="{{ $method->daily_limit }}" class="rounded-xl border border-slate-300 px-3 py-2">
                            </label>
                            <label class="flex items-center gap-2 text-sm font-medium text-slate-700">
                                <input type="hidden" name="auto_approval" value="0">
                                <input type="checkbox" name="auto_approval" value="1" @checked($method->auto_approval) class="rounded border-slate-300">
                                Auto approval
                            </label>
                            <label class="flex items-center gap-2 text-sm font-medium text-slate-700">
                                <input type="hidden" name="weekend_processing" value="0">
                                <input type="checkbox" name="weekend_processing" value="1" @checked($method->weekend_processing) class="rounded border-slate-300">
                                Weekend processing
                            </label>
                            <label class="flex flex-col gap-1 text-sm md:col-span-3">
                                <span class="font-medium text-slate-700">Public Description</span>
                                <input type="text" name="public_description" value="{{ $method->public_description }}" class="rounded-xl border border-slate-300 px-3 py-2">
                            </label>
                            <label class="flex flex-col gap-1 text-sm md:col-span-3">
                                <span class="font-medium text-slate-700">Internal Notes</span>
                                <textarea name="internal_notes" rows="2" class="rounded-xl border border-slate-300 px-3 py-2">{{ $method->internal_notes }}</textarea>
                            </label>
                            <div class="md:col-span-3">
                                <button type="submit" class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Save changes</button>
                            </div>
                        </form>
                    </div>
                @empty
                    <p class="text-slate-600">No settlement methods configured yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
