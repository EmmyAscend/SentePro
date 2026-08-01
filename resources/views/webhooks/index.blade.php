<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-white">Webhook Events</h2>
                <p class="text-sm text-slate-400">Capture and inspect incoming provider callbacks</p>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-6 lg:grid-cols-[1fr_1fr]">
                <div class="rounded-2xl bg-slate-900 p-6 shadow-sm ring-1 ring-white/10">
                    <h3 class="text-lg font-semibold text-white">Store webhook event</h3>
                    <form method="POST" action="{{ route('webhooks.store') }}" class="mt-4 space-y-4">
                        @csrf

                        <div>
                            <label class="block text-sm font-medium text-slate-300">Business</label>
                            <select name="business_id" class="mt-1 w-full rounded-xl border border-white/10 bg-slate-950 px-3 py-2 text-white">
                                @foreach ($events as $event)
                                    <option value="{{ $event->business_id }}">{{ $event->business->business_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-300">Provider</label>
                            <input type="text" name="provider" class="mt-1 w-full rounded-xl border border-white/10 bg-slate-950 px-3 py-2 text-white" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-300">Event</label>
                            <input type="text" name="event" class="mt-1 w-full rounded-xl border border-white/10 bg-slate-950 px-3 py-2 text-white" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-300">Payload</label>
                            <textarea name="payload" rows="5" class="mt-1 w-full rounded-xl border border-white/10 bg-slate-950 px-3 py-2 text-white" required></textarea>
                        </div>

                        <button type="submit" class="rounded-xl bg-lime-400 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-lime-300">Capture webhook</button>
                    </form>
                </div>

                <div class="rounded-2xl bg-slate-900 p-6 shadow-sm ring-1 ring-white/10">
                    <h3 class="text-lg font-semibold text-white">Incoming events</h3>
                    <div class="mt-4 space-y-3">
                        @forelse ($events as $event)
                            <div class="rounded-xl bg-slate-800/60 p-4">
                                <div class="flex items-center justify-between gap-3">
                                    <div>
                                        <p class="font-semibold text-white">{{ $event->event }}</p>
                                        <p class="text-sm text-slate-400">{{ $event->provider }} • {{ $event->business->business_name }}</p>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-slate-400">No webhook events have been captured yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
