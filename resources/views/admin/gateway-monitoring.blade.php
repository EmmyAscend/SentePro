<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-white">Gateway Monitoring</h2>
            <p class="text-sm text-slate-400">Health and call logs across every business's payment gateways</p>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="rounded-2xl bg-slate-900 p-6 shadow-sm ring-1 ring-white/10">
                <h3 class="text-lg font-semibold text-white">Gateway health</h3>
                <div class="mt-4 space-y-3">
                    @forelse ($providers as $provider)
                        <div class="rounded-xl bg-slate-800/60 px-4 py-3">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <p class="font-semibold text-white">{{ $provider->name }}</p>
                                    <p class="text-sm text-slate-400">{{ $provider->business->business_name }} • {{ $provider->provider->label() }} • {{ $provider->environment }}</p>
                                </div>
                                <div class="flex items-center gap-3 text-sm text-slate-400">
                                    @if ($provider->last_health_status === 'healthy')
                                        <span class="rounded-full bg-emerald-500/15 px-3 py-1 text-xs font-semibold text-emerald-300">HEALTHY</span>
                                    @elseif ($provider->last_health_status === 'unhealthy')
                                        <span class="rounded-full bg-rose-500/15 px-3 py-1 text-xs font-semibold text-rose-300">UNHEALTHY</span>
                                    @else
                                        <span class="rounded-full bg-slate-500/15 px-3 py-1 text-xs font-semibold text-slate-300">NOT YET CHECKED</span>
                                    @endif
                                    @if ($provider->last_latency_ms !== null)
                                        <span>{{ $provider->last_latency_ms }}ms</span>
                                    @endif
                                    @if ($provider->last_checked_at)
                                        <span>{{ $provider->last_checked_at->diffForHumans() }}</span>
                                    @endif
                                </div>
                            </div>
                            @if ($provider->last_error)
                                <p class="mt-2 text-xs text-rose-400">{{ $provider->last_error }}</p>
                            @endif
                        </div>
                    @empty
                        <p class="text-slate-400">No gateway providers configured yet.</p>
                    @endforelse
                </div>
            </div>

            <div class="rounded-2xl bg-slate-900 p-6 shadow-sm ring-1 ring-white/10">
                <h3 class="text-lg font-semibold text-white">Recent gateway calls</h3>
                <p class="text-sm text-slate-400">Latest 200 across every business</p>
                <div class="mt-4 overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="text-xs uppercase tracking-wide text-slate-400">
                                <th class="py-2 pr-4">Business</th>
                                <th class="py-2 pr-4">Gateway</th>
                                <th class="py-2 pr-4">Method</th>
                                <th class="py-2 pr-4">Result</th>
                                <th class="py-2 pr-4">Latency</th>
                                <th class="py-2 pr-4">Error</th>
                                <th class="py-2 pr-4">When</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($logs as $log)
                                <tr class="border-t border-white/10">
                                    <td class="py-2 pr-4">{{ $log->business->business_name }}</td>
                                    <td class="py-2 pr-4">{{ $log->gatewayProvider->name }}</td>
                                    <td class="py-2 pr-4">{{ $log->method }}</td>
                                    <td class="py-2 pr-4">
                                        @if ($log->success)
                                            <span class="rounded-full bg-emerald-500/15 px-2 py-0.5 text-xs font-semibold text-emerald-300">OK</span>
                                        @else
                                            <span class="rounded-full bg-rose-500/15 px-2 py-0.5 text-xs font-semibold text-rose-300">FAILED</span>
                                        @endif
                                    </td>
                                    <td class="py-2 pr-4">{{ $log->latency_ms }}ms</td>
                                    <td class="py-2 pr-4 text-xs text-rose-400">{{ $log->error }}</td>
                                    <td class="py-2 pr-4 text-xs text-slate-400">{{ $log->created_at->diffForHumans() }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-4 text-slate-400">No gateway calls logged yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
