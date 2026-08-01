<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-900">{{ $ticket->subject }}</h2>
                <p class="text-sm text-slate-500">
                    {{ $ticket->business->business_name }} • Ticket #{{ $ticket->id }}
                </p>
            </div>
            <a href="{{ route('support-tickets.index') }}" class="rounded-full border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Back to tickets</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                <div class="flex items-center justify-between gap-4">
                    <span @class([
                        'rounded-full px-3 py-1 text-xs font-semibold',
                        'bg-amber-100 text-amber-700' => $ticket->status === \App\Enums\SupportTicketStatus::Open,
                        'bg-emerald-100 text-emerald-700' => $ticket->status === \App\Enums\SupportTicketStatus::Resolved,
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
                                    <button type="submit" class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700 hover:bg-amber-200">Reopen</button>
                                </form>
                            @endif
                        </div>
                    @endcan
                </div>

                <div class="mt-6 space-y-4">
                    @foreach ($ticket->messages as $message)
                        <div class="rounded-xl bg-slate-50 p-4">
                            <div class="flex items-center gap-2">
                                <p class="font-semibold text-slate-900">{{ $message->user?->name ?? 'Deleted user' }}</p>
                                @if ($message->user?->isSuperAdmin())
                                    <span class="rounded-full bg-slate-900 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-white">Support</span>
                                @endif
                                <span class="text-xs text-slate-400">{{ $message->created_at->format('d M Y, H:i') }}</span>
                            </div>
                            <p class="mt-2 whitespace-pre-line text-sm text-slate-700">{{ $message->body }}</p>
                        </div>
                    @endforeach
                </div>

                @can('update', $ticket)
                    <form method="POST" action="{{ route('support-tickets.messages.store', $ticket) }}" class="mt-6 space-y-3">
                        @csrf
                        <textarea name="body" rows="3" class="w-full rounded-xl border border-slate-300 px-3 py-2" placeholder="Write a reply..." required></textarea>
                        <button type="submit" class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Send reply</button>
                    </form>
                @endcan
            </div>
        </div>
    </div>
</x-app-layout>
