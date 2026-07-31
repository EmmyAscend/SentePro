<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-900">Gateway Providers</h2>
                <p class="text-sm text-slate-500">Configure payment providers for the business collection engine</p>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-6 lg:grid-cols-[1fr_1fr]">
                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                    <h3 class="text-lg font-semibold text-slate-900">Add provider</h3>
                    <form method="POST" action="{{ route('gateways.store') }}" class="mt-4 space-y-4">
                        @csrf

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Business</label>
                            <select name="business_id" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2">
                                @foreach ($providers as $provider)
                                    <option value="{{ $provider->business_id }}">{{ $provider->business->business_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Name</label>
                            <input type="text" name="name" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Provider</label>
                            <select name="provider" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2" required>
                                <option value="pesapal">Pesapal (cards)</option>
                                <option value="yo_payments">Yo Payments (MTN / Airtel mobile money)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Status</label>
                            <select name="status" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Environment</label>
                            <select name="environment" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2">
                                <option value="sandbox">Sandbox</option>
                                <option value="production">Production</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Credentials (JSON)</label>
                            <textarea name="credentials" rows="3" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 font-mono text-xs" placeholder='Pesapal: {"consumer_key": "...", "consumer_secret": "..."}&#10;Yo Payments: {"api_username": "...", "api_password": "..."}' required></textarea>
                            <p class="mt-1 text-xs text-slate-500">Pesapal needs <code>consumer_key</code>/<code>consumer_secret</code>. Yo Payments needs <code>api_username</code>/<code>api_password</code>. The webhook URL is generated automatically — you don't enter one.</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Supported countries</label>
                            <input type="text" name="supported_countries" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Supported currencies</label>
                            <input type="text" name="supported_currencies" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2" required>
                        </div>

                        <button type="submit" class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Save provider</button>
                    </form>
                </div>

                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                    <h3 class="text-lg font-semibold text-slate-900">Configured providers</h3>
                    <div class="mt-4 space-y-3">
                        @forelse ($providers as $provider)
                            <div class="rounded-xl bg-slate-50 p-4">
                                <div class="flex items-center justify-between gap-3">
                                    <div>
                                        <p class="font-semibold text-slate-900">{{ $provider->name }}</p>
                                        <p class="text-sm text-slate-500">{{ $provider->provider->label() }} • {{ $provider->environment }}</p>
                                    </div>
                                    <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">{{ strtoupper($provider->status) }}</span>
                                </div>
                            </div>
                        @empty
                            <p class="text-slate-600">No gateways configured yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
