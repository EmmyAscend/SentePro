<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-slate-900">Support</h2>
            <p class="text-sm text-slate-500">Talk to the SentePro support team directly from your dashboard</p>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 space-y-6">
            @can('create', \App\Models\SupportTicket::class)
                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                    <h3 class="text-lg font-semibold text-slate-900">Open a new ticket</h3>
                    <form method="POST" action="{{ route('support-tickets.store') }}" class="mt-4 space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Subject</label>
                            <input type="text" name="subject" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Message</label>
                            <textarea name="body" rows="4" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2" required></textarea>
                        </div>
                        <button type="submit" class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Open ticket</button>
                    </form>
                </div>
            @endcan

            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                <h3 class="text-lg font-semibold text-slate-900">Tickets</h3>
                <div class="mt-4 space-y-3">
                    @forelse ($tickets as $ticket)
                        <a href="{{ route('support-tickets.show', $ticket) }}" class="block rounded-xl bg-slate-50 p-4 hover:bg-slate-100">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="font-semibold text-slate-900">{{ $ticket->subject }}</p>
                                    <p class="text-sm text-slate-500">
                                        @if (auth()->user()->isSuperAdmin())
                                            {{ $ticket->business->business_name }} •
                                        @endif
                                        Updated {{ $ticket->updated_at->diffForHumans() }}
                                    </p>
                                </div>
                                <span @class([
                                    'rounded-full px-3 py-1 text-xs font-semibold',
                                    'bg-amber-100 text-amber-700' => $ticket->status === \App\Enums\SupportTicketStatus::Open,
                                    'bg-emerald-100 text-emerald-700' => $ticket->status === \App\Enums\SupportTicketStatus::Resolved,
                                ])>{{ $ticket->status->label() }}</span>
                            </div>
                        </a>
                    @empty
                        <p class="text-slate-600">No support tickets yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
