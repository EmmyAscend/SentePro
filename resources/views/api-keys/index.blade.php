<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-white">API Keys</h2>
            <p class="text-sm text-slate-400">Issue bearer tokens to integrate SentePro with your own systems</p>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8 space-y-6">
            @if (session('plainTextToken'))
                <div class="rounded-2xl bg-emerald-500/10 p-6 ring-1 ring-emerald-500/30">
                    <p class="font-semibold text-emerald-300">Copy your new API key now — it won't be shown again.</p>
                    <code class="mt-2 block break-all rounded-xl bg-slate-900 p-3 text-sm text-emerald-300">{{ session('plainTextToken') }}</code>
                </div>
            @endif

            <div class="rounded-2xl bg-slate-900 p-6 shadow-sm ring-1 ring-white/10">
                <h3 class="text-lg font-semibold text-white">Create a new key</h3>
                <form method="POST" action="{{ route('api-keys.store') }}" class="mt-4 flex items-end gap-3">
                    @csrf
                    <label class="flex-1 flex flex-col gap-1 text-sm">
                        <span class="font-medium text-slate-300">Name</span>
                        <input type="text" name="name" placeholder="e.g. Production backend" class="rounded-xl border border-white/10 bg-slate-950 px-3 py-2 text-white" required>
                    </label>
                    <button type="submit" class="rounded-xl bg-lime-400 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-lime-300">Create key</button>
                </form>
            </div>

            <div class="rounded-2xl bg-slate-900 p-6 shadow-sm ring-1 ring-white/10">
                <h3 class="text-lg font-semibold text-white">Your keys</h3>
                <div class="mt-4 space-y-3">
                    @forelse ($tokens as $token)
                        <div class="flex items-center justify-between gap-3 rounded-xl bg-slate-800/60 p-4">
                            <div>
                                <p class="font-semibold text-white">{{ $token->name }}</p>
                                <p class="text-sm text-slate-400">
                                    Created {{ $token->created_at->diffForHumans() }} •
                                    Last used {{ $token->last_used_at?->diffForHumans() ?? 'never' }}
                                </p>
                            </div>
                            <form method="POST" action="{{ route('api-keys.destroy', $token) }}" onsubmit="return confirm('Revoke this API key? Anything using it will stop working immediately.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="rounded-full bg-rose-500/15 px-3 py-1 text-xs font-semibold text-rose-300 hover:bg-rose-500/25">Revoke</button>
                            </form>
                        </div>
                    @empty
                        <p class="text-slate-400">No API keys yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
