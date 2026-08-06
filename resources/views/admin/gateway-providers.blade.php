<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-white">Payment Gateways</h2>
            <p class="text-sm text-slate-400">SentePro's own Pesapal (cards) and Yo Payments (mobile money) credentials — used for every business, never shown to businesses or their customers</p>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <p class="rounded-xl bg-emerald-500/15 px-4 py-3 text-sm font-medium text-emerald-300">{{ session('status') }}</p>
            @endif

            @foreach ($providers as $provider)
                <div x-data="{ editing: false }" class="rounded-2xl bg-slate-900 p-6 shadow-sm ring-1 ring-white/10">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <p class="font-semibold text-white">{{ $provider->provider->label() }}</p>
                            <p class="mt-1 text-sm text-slate-400">
                                {{ $provider->provider === \App\Enums\PaymentProvider::Pesapal ? 'Used for Debit/Credit Card payments' : 'Used for Mobile Money payments' }}
                                • {{ $provider->environment }}
                            </p>
                            <div class="mt-2 flex flex-wrap items-center gap-2 text-xs text-slate-400">
                                <span class="rounded-full px-2 py-0.5 font-semibold {{ $provider->status === 'active' ? 'bg-emerald-500/15 text-emerald-300' : 'bg-slate-500/15 text-slate-300' }}">{{ strtoupper($provider->status) }}</span>
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
                            </div>
                            @if ($provider->last_error)
                                <p class="mt-1 text-xs text-rose-400">{{ $provider->last_error }}</p>
                            @endif
                        </div>
                        <div class="flex shrink-0 items-center gap-2">
                            <form method="POST" action="{{ route('admin.gateway-providers.test', $provider) }}">
                                @csrf
                                <button type="submit" class="rounded-full border border-white/10 px-3 py-1 text-xs font-semibold text-slate-300 hover:bg-white/5">Test connection</button>
                            </form>
                            <button type="button" @click="editing = !editing" class="rounded-full border border-white/10 px-3 py-1 text-xs font-semibold text-slate-300 hover:bg-white/5">Edit</button>
                        </div>
                    </div>

                    <form x-show="editing" x-cloak method="POST" action="{{ route('admin.gateway-providers.update', $provider) }}" class="mt-4 space-y-4 border-t border-white/10 pt-4">
                        @csrf
                        @method('PUT')

                        <div>
                            <label class="block text-sm font-medium text-slate-300">Status</label>
                            <select name="status" class="mt-1 w-full rounded-xl border border-white/10 bg-slate-950 px-3 py-2 text-white">
                                <option value="active" @selected($provider->status === 'active')>Active</option>
                                <option value="inactive" @selected($provider->status === 'inactive')>Inactive</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-300">Environment</label>
                            <select name="environment" class="mt-1 w-full rounded-xl border border-white/10 bg-slate-950 px-3 py-2 text-white">
                                <option value="sandbox" @selected($provider->environment === 'sandbox')>Sandbox</option>
                                <option value="production" @selected($provider->environment === 'production')>Production</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-300">Credentials (JSON)</label>
                            <textarea name="credentials" rows="3" class="mt-1 w-full rounded-xl border border-white/10 bg-slate-950 px-3 py-2 font-mono text-xs text-white" placeholder='{{ $provider->provider === \App\Enums\PaymentProvider::Pesapal ? '{"consumer_key": "...", "consumer_secret": "..."}' : '{"api_username": "...", "api_password": "..."}' }}' required>{{ json_encode($provider->credentials ?: new stdClass) }}</textarea>
                            <p class="mt-1 text-xs text-slate-400">
                                @if ($provider->provider === \App\Enums\PaymentProvider::Pesapal)
                                    Needs <code>consumer_key</code>/<code>consumer_secret</code>.
                                @else
                                    Needs <code>api_username</code>/<code>api_password</code>.
                                @endif
                                The webhook URL is generated automatically.
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-300">Supported currencies</label>
                            <input type="text" name="supported_currencies" value="{{ $provider->supported_currencies }}" class="mt-1 w-full rounded-xl border border-white/10 bg-slate-950 px-3 py-2 text-white" required>
                        </div>

                        <button type="submit" class="rounded-xl bg-lime-400 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-lime-300">Save changes</button>
                    </form>
                </div>
            @endforeach
        </div>
    </div>
</x-app-layout>
