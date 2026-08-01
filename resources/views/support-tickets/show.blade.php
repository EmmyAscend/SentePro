<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-white">{{ $ticket->subject }}</h2>
                <p class="text-sm text-slate-400">
                    {{ $ticket->business->business_name }} • Ticket #{{ $ticket->id }}
                </p>
            </div>
            <a href="{{ route('support-tickets.index') }}" class="rounded-full border border-white/10 px-4 py-2 text-sm font-semibold text-slate-300 hover:bg-white/5">Back to tickets</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="rounded-2xl bg-slate-900 p-6 shadow-sm ring-1 ring-white/10">
                <div class="flex items-center justify-between gap-4">
                    <span @class([
                        'rounded-full px-3 py-1 text-xs font-semibold',
                        'bg-amber-500/15 text-amber-300' => $ticket->status === \App\Enums\SupportTicketStatus::Open,
                        'bg-emerald-500/15 text-emerald-300' => $ticket->status === \App\Enums\SupportTicketStatus::Resolved,
                    ])>{{ $ticket->status->label() }}</span>

                    @can('update', $ticket)
                        <div class="flex gap-2">
                            @if ($ticket->status === \App\Enums\SupportTicketStatus::Open)
                                <form method="POST" action="{{ route('support-tickets.resolve', $ticket) }}">
                                    @csrf
                                    <button type="submit" class="rounded-full bg-emerald-500 px-3 py-1 text-xs font-semibold text-slate-950 hover:bg-emerald-400">Mark resolved</button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('support-tickets.reopen', $ticket) }}">
                                    @csrf
                                    <button type="submit" class="rounded-full bg-amber-500/15 px-3 py-1 text-xs font-semibold text-amber-300 hover:bg-amber-500/25">Reopen</button>
                                </form>
                            @endif
                        </div>
                    @endcan
                </div>

                <div class="mt-6 space-y-4">
                    @foreach ($ticket->messages as $message)
                        <div class="rounded-xl bg-slate-800/60 p-4">
                            <div class="flex items-center gap-2">
                                <p class="font-semibold text-white">{{ $message->user?->name ?? 'Deleted user' }}</p>
                                @if ($message->user?->isSuperAdmin())
                                    <span class="rounded-full bg-slate-900 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-white">Support</span>
                                @endif
                                <span class="text-xs text-slate-400">{{ $message->created_at->format('d M Y, H:i') }}</span>
                            </div>
                            <p class="mt-2 whitespace-pre-line text-sm text-slate-300">{{ $message->body }}</p>
                        </div>
                    @endforeach
                </div>

                @can('update', $ticket)
                    <form method="POST" action="{{ route('support-tickets.messages.store', $ticket) }}" class="mt-6 space-y-3">
                        @csrf
                        <textarea name="body" rows="3" class="w-full rounded-xl border border-white/10 bg-slate-950 text-white px-3 py-2" placeholder="Write a reply..." required></textarea>
                        <button type="submit" class="rounded-xl bg-lime-400 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-lime-300">Send reply</button>
                    </form>
                @endcan
            </div>
        </div>
    </div>
</x-app-layout>
