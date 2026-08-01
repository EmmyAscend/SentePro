<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-white">{{ $dispute->reason }}</h2>
                <p class="text-sm text-slate-400">
                    {{ $dispute->business->business_name }} • Dispute #{{ $dispute->id }} • Transaction {{ $dispute->paymentTransaction->external_reference }}
                </p>
            </div>
            <a href="{{ route('disputes.index') }}" class="rounded-full border border-white/10 px-4 py-2 text-sm font-semibold text-slate-300 hover:bg-white/5">Back to disputes</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="rounded-2xl bg-slate-900 p-6 shadow-sm ring-1 ring-white/10">
                <span @class([
                    'rounded-full px-3 py-1 text-xs font-semibold',
                    'bg-amber-500/15 text-amber-300' => $dispute->status === \App\Enums\DisputeStatus::Open,
                    'bg-emerald-500/15 text-emerald-300' => $dispute->status === \App\Enums\DisputeStatus::Resolved,
                    'bg-rose-500/15 text-rose-300' => $dispute->status === \App\Enums\DisputeStatus::Rejected,
                ])>{{ $dispute->status->label() }}</span>

                <div class="mt-4 space-y-2 text-sm text-slate-300">
                    <p><span class="font-medium text-white">Raised by:</span> {{ $dispute->raisedBy?->name ?? 'Deleted user' }}</p>
                    @if ($dispute->description)
                        <p><span class="font-medium text-white">Description:</span> {{ $dispute->description }}</p>
                    @endif
                </div>

                @if ($dispute->status !== \App\Enums\DisputeStatus::Open)
                    <div class="mt-4 rounded-xl bg-slate-800/60 p-4 text-sm text-slate-300">
                        <p class="font-medium text-white">Resolution notes</p>
                        <p class="mt-1">{{ $dispute->resolution_notes }}</p>
                        <p class="mt-2 text-xs text-slate-400">By {{ $dispute->resolvedBy?->name ?? 'Deleted user' }} on {{ $dispute->resolved_at?->format('d M Y, H:i') }}</p>
                    </div>
                @endif

                @can('process', $dispute)
                    @if ($dispute->status === \App\Enums\DisputeStatus::Open)
                        <div class="mt-6 grid gap-4 sm:grid-cols-2">
                            <form method="POST" action="{{ route('admin.disputes.resolve', $dispute) }}" class="space-y-2">
                                @csrf
                                <textarea name="notes" rows="3" class="w-full rounded-xl border border-white/10 bg-slate-950 text-white px-3 py-2 text-sm" placeholder="Resolution notes" required></textarea>
                                <button type="submit" class="w-full rounded-xl bg-emerald-500 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-emerald-400">Resolve</button>
                            </form>
                            <form method="POST" action="{{ route('admin.disputes.reject', $dispute) }}" class="space-y-2">
                                @csrf
                                <textarea name="notes" rows="3" class="w-full rounded-xl border border-white/10 bg-slate-950 text-white px-3 py-2 text-sm" placeholder="Reason for rejecting" required></textarea>
                                <button type="submit" class="w-full rounded-xl bg-rose-500/15 px-4 py-2 text-sm font-semibold text-rose-300 hover:bg-rose-500/25">Reject</button>
                            </form>
                        </div>
                    @endif
                @endcan
            </div>
        </div>
    </div>
</x-app-layout>
