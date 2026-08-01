<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-white">Support</h2>
            <p class="text-sm text-slate-400">Talk to the SentePro support team directly from your dashboard</p>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 space-y-6">
            @can('create', \App\Models\SupportTicket::class)
                <div class="rounded-2xl bg-slate-900 p-6 shadow-sm ring-1 ring-white/10">
                    <h3 class="text-lg font-semibold text-white">Open a new ticket</h3>
                    <form method="POST" action="{{ route('support-tickets.store') }}" class="mt-4 space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-slate-300">Subject</label>
                            <input type="text" name="subject" class="mt-1 w-full rounded-xl border border-white/10 bg-slate-950 text-white px-3 py-2" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300">Message</label>
                            <textarea name="body" rows="4" class="mt-1 w-full rounded-xl border border-white/10 bg-slate-950 text-white px-3 py-2" required></textarea>
                        </div>
                        <button type="submit" class="rounded-xl bg-lime-400 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-lime-300">Open ticket</button>
                    </form>
                </div>
            @endcan

            <div class="rounded-2xl bg-slate-900 p-6 shadow-sm ring-1 ring-white/10">
                <h3 class="text-lg font-semibold text-white">Tickets</h3>
                <div class="mt-4 space-y-3">
                    @forelse ($tickets as $ticket)
                        <a href="{{ route('support-tickets.show', $ticket) }}" class="block rounded-xl bg-slate-800/60 p-4 hover:bg-white/5">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="font-semibold text-white">{{ $ticket->subject }}</p>
                                    <p class="text-sm text-slate-400">
                                        @if (auth()->user()->isSuperAdmin())
                                            {{ $ticket->business->business_name }} •
                                        @endif
                                        Updated {{ $ticket->updated_at->diffForHumans() }}
                                    </p>
                                </div>
                                <span @class([
                                    'rounded-full px-3 py-1 text-xs font-semibold',
                                    'bg-amber-500/15 text-amber-300' => $ticket->status === \App\Enums\SupportTicketStatus::Open,
                                    'bg-emerald-500/15 text-emerald-300' => $ticket->status === \App\Enums\SupportTicketStatus::Resolved,
                                ])>{{ $ticket->status->label() }}</span>
                            </div>
                        </a>
                    @empty
                        <p class="text-slate-400">No support tickets yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
