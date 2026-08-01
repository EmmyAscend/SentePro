<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-white">Gateway Providers</h2>
                <p class="text-sm text-slate-400">Configure payment providers for the business collection engine</p>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-6 lg:grid-cols-[1fr_1fr]">
                <div class="rounded-2xl bg-slate-900 p-6 shadow-sm ring-1 ring-white/10">
                    <h3 class="text-lg font-semibold text-white">Add provider</h3>
                    <form method="POST" action="{{ route('gateways.store') }}" class="mt-4 space-y-4">
                        @csrf

                        <div>
                            <label class="block text-sm font-medium text-slate-300">Business</label>
                            <select name="business_id" class="mt-1 w-full rounded-xl border border-white/10 bg-slate-950 px-3 py-2 text-white">
                                @foreach ($providers as $provider)
                                    <option value="{{ $provider->business_id }}">{{ $provider->business->business_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-300">Name</label>
                            <input type="text" name="name" class="mt-1 w-full rounded-xl border border-white/10 bg-slate-950 px-3 py-2 text-white" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-300">Provider</label>
                            <select name="provider" class="mt-1 w-full rounded-xl border border-white/10 bg-slate-950 px-3 py-2 text-white" required>
                                <option value="pesapal">Pesapal (cards)</option>
                                <option value="yo_payments">Yo Payments (MTN / Airtel mobile money)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-300">Status</label>
                            <select name="status" class="mt-1 w-full rounded-xl border border-white/10 bg-slate-950 px-3 py-2 text-white">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-300">Environment</label>
                            <select name="environment" class="mt-1 w-full rounded-xl border border-white/10 bg-slate-950 px-3 py-2 text-white">
                                <option value="sandbox">Sandbox</option>
                                <option value="production">Production</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-300">Credentials (JSON)</label>
                            <textarea name="credentials" rows="3" class="mt-1 w-full rounded-xl border border-white/10 bg-slate-950 px-3 py-2 font-mono text-xs text-white" placeholder='Pesapal: {"consumer_key": "...", "consumer_secret": "..."}&#10;Yo Payments: {"api_username": "...", "api_password": "..."}' required></textarea>
                            <p class="mt-1 text-xs text-slate-400">Pesapal needs <code>consumer_key</code>/<code>consumer_secret</code>. Yo Payments needs <code>api_username</code>/<code>api_password</code>. The webhook URL is generated automatically — you don't enter one.</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-300">Supported countries</label>
                            <input type="text" name="supported_countries" class="mt-1 w-full rounded-xl border border-white/10 bg-slate-950 px-3 py-2 text-white" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-300">Supported currencies</label>
                            <input type="text" name="supported_currencies" class="mt-1 w-full rounded-xl border border-white/10 bg-slate-950 px-3 py-2 text-white" required>
                        </div>

                        <button type="submit" class="rounded-xl bg-lime-400 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-lime-300">Save provider</button>
                    </form>
                </div>

                <div class="rounded-2xl bg-slate-900 p-6 shadow-sm ring-1 ring-white/10">
                    <h3 class="text-lg font-semibold text-white">Configured providers</h3>
                    <div class="mt-4 space-y-3">
                        @forelse ($providers as $provider)
                            <div class="rounded-xl bg-slate-800/60 p-4">
                                <div class="flex items-center justify-between gap-3">
                                    <div>
                                        <p class="font-semibold text-white">{{ $provider->name }}</p>
                                        <p class="text-sm text-slate-400">{{ $provider->provider->label() }} • {{ $provider->environment }}</p>
                                    </div>
                                    <span class="rounded-full bg-emerald-500/15 px-3 py-1 text-xs font-semibold text-emerald-300">{{ strtoupper($provider->status) }}</span>
                                </div>

                                <div class="mt-3 flex flex-wrap items-center gap-2 text-xs text-slate-400">
                                    @if ($provider->last_health_status === 'healthy')
                                        <span class="rounded-full bg-emerald-500/15 px-2 py-0.5 font-semibold text-emerald-300">HEALTHY</span>
                                    @elseif ($provider->last_health_status === 'unhealthy')
                                        <span class="rounded-full bg-rose-500/15 px-2 py-0.5 font-semibold text-rose-300">UNHEALTHY</span>
                                    @else
                                        <span class="rounded-full bg-slate-500/15 px-2 py-0.5 font-semibold text-slate-300">NOT YET CHECKED</span>
                                    @endif
                                    @if ($provider->last_checked_at)
                                        <span>Checked {{ $provider->last_checked_at->diffForHumans() }} ({{ $provider->last_latency_ms }}ms)</span>
                                    @endif
                                    @can('update', $provider)
                                        <form method="POST" action="{{ route('gateways.test', $provider) }}">
                                            @csrf
                                            <button type="submit" class="rounded-full border border-white/10 px-2 py-0.5 font-semibold text-slate-300 hover:bg-white/5">Test connection</button>
                                        </form>
                                    @endcan
                                </div>
                            </div>
                        @empty
                            <p class="text-slate-400">No gateways configured yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
