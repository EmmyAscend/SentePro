<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-slate-900">Disputes</h2>
            <p class="text-sm text-slate-500">Transactions flagged for review</p>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                <h3 class="text-lg font-semibold text-slate-900">All disputes</h3>
                <div class="mt-4 space-y-3">
                    @forelse ($disputes as $dispute)
                        <a href="{{ route('disputes.show', $dispute) }}" class="block rounded-xl bg-slate-50 p-4 hover:bg-slate-100">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="font-semibold text-slate-900">{{ $dispute->reason }}</p>
                                    <p class="text-sm text-slate-500">
                                        @if (auth()->user()->isSuperAdmin())
                                            {{ $dispute->business->business_name }} •
                                        @endif
                                        {{ $dispute->paymentTransaction->external_reference }} •
                                        Updated {{ $dispute->updated_at->diffForHumans() }}
                                    </p>
                                </div>
                                <span @class([
                                    'rounded-full px-3 py-1 text-xs font-semibold',
                                    'bg-amber-100 text-amber-700' => $dispute->status === \App\Enums\DisputeStatus::Open,
                                    'bg-emerald-100 text-emerald-700' => $dispute->status === \App\Enums\DisputeStatus::Resolved,
                                    'bg-rose-100 text-rose-700' => $dispute->status === \App\Enums\DisputeStatus::Rejected,
                                ])>{{ $dispute->status->label() }}</span>
                            </div>
                        </a>
                    @empty
                        <p class="text-slate-600">No disputes yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
