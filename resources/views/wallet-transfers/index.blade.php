<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-slate-900">Wallet Transfers</h2>
            <p class="text-sm text-slate-500">Move money to another SentePro business instantly — no gateway, no fee</p>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 space-y-6">
            @can('create', \App\Models\WalletTransfer::class)
                <div class="grid gap-6 lg:grid-cols-[1.15fr_0.85fr]">
                    <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                        <h3 class="text-lg font-semibold text-slate-900">Send money</h3>
                        <form method="POST" action="{{ route('wallet-transfers.store') }}" class="mt-4 space-y-4">
                            @csrf
                            <div>
                                <label class="block text-sm font-medium text-slate-700">Recipient</label>
                                <input type="text" name="recipient" value="{{ old('recipient', request('recipient')) }}" placeholder="Business ID, email, or phone" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700">Amount</label>
                                <input type="number" min="1" step="0.01" name="amount" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700">Note</label>
                                <textarea name="note" rows="3" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2" placeholder="Optional note"></textarea>
                            </div>
                            <button type="submit" class="w-full rounded-xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white">Send transfer</button>
                        </form>
                    </div>

                    <div class="rounded-2xl bg-slate-900 p-6 text-white shadow-lg shadow-slate-950/20">
                        <h3 class="text-lg font-semibold">Receive money</h3>
                        <p class="mt-1 text-sm text-slate-300">Share your Business ID or let another business scan this code</p>
                        <div class="mt-4 flex justify-center">
                            <img src="{{ route('wallet-transfers.receive-qr-code') }}" alt="Scan to send this business money" width="160" height="160" class="h-40 w-40 rounded-lg bg-white p-1">
                        </div>
                        <div class="mt-4 rounded-xl bg-slate-800 p-4 text-center">
                            <p class="text-xs text-slate-400">Your Business ID</p>
                            <p class="text-2xl font-bold">{{ auth()->user()->business_id }}</p>
                        </div>
                    </div>
                </div>
            @endcan

            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                <h3 class="text-lg font-semibold text-slate-900">Transfer history</h3>
                <div class="mt-4 space-y-3">
                    @forelse ($transfers as $transfer)
                        @php
                            $isSender = $transfer->sender_business_id === auth()->user()->business_id;
                        @endphp
                        <div class="rounded-xl bg-slate-50 p-4">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="font-semibold text-slate-900">
                                        @if (auth()->user()->isSuperAdmin())
                                            {{ $transfer->senderBusiness->business_name }} → {{ $transfer->recipientBusiness->business_name }}
                                        @elseif ($isSender)
                                            Sent to {{ $transfer->recipientBusiness->business_name }}
                                        @else
                                            Received from {{ $transfer->senderBusiness->business_name }}
                                        @endif
                                    </p>
                                    <p class="text-sm text-slate-500">{{ $transfer->reference }} • {{ $transfer->created_at->format('d M Y, H:i') }}</p>
                                    @if ($transfer->note)
                                        <p class="mt-1 text-sm text-slate-600">{{ $transfer->note }}</p>
                                    @endif
                                </div>
                                <span @class([
                                    'rounded-full px-3 py-1 text-xs font-semibold',
                                    'bg-rose-100 text-rose-700' => $isSender && ! auth()->user()->isSuperAdmin(),
                                    'bg-emerald-100 text-emerald-700' => ! $isSender && ! auth()->user()->isSuperAdmin(),
                                    'bg-slate-200 text-slate-600' => auth()->user()->isSuperAdmin(),
                                ])>
                                    {{ auth()->user()->isSuperAdmin() ? '' : ($isSender ? '-' : '+') }}{{ number_format($transfer->amount, 2) }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <p class="text-slate-600">No wallet transfers yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
