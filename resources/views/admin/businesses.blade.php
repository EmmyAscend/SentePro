<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-white">Businesses</h2>
                <p class="text-sm text-slate-400">Every registered business, across all statuses</p>
            </div>
            <a href="{{ route('business.register') }}" class="rounded-full bg-lime-400 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-lime-300">New business</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="rounded-2xl bg-slate-900 p-6 shadow-sm ring-1 ring-white/10">
                <form method="GET" action="{{ route('admin.businesses.index') }}" class="flex flex-wrap items-end gap-4">
                    <label class="flex flex-col gap-1 text-sm">
                        <span class="font-medium text-slate-300">Status</span>
                        <select name="status" class="rounded-xl border border-white/10 bg-slate-950 text-white px-3 py-2">
                            <option value="">All statuses</option>
                            @foreach (['pending', 'under_review', 'approved', 'rejected', 'suspended'] as $status)
                                <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                            @endforeach
                        </select>
                    </label>
                    <button type="submit" class="rounded-xl bg-lime-400 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-lime-300">Filter</button>
                    <a href="{{ route('admin.businesses.index') }}" class="rounded-xl border border-white/10 px-4 py-2 text-sm font-semibold text-slate-300 hover:bg-white/5">Clear</a>
                </form>
            </div>

            <div class="rounded-2xl bg-slate-900 p-6 shadow-sm ring-1 ring-white/10">
                <h3 class="text-lg font-semibold text-white">All businesses</h3>
                @if ($businesses->isEmpty())
                    <p class="mt-4 text-slate-400">No businesses match this filter.</p>
                @else
                    <div class="mt-4 space-y-3">
                        @foreach ($businesses as $business)
                            <div class="rounded-xl bg-slate-800/60 px-4 py-3">
                                <div class="flex flex-wrap items-center justify-between gap-3">
                                    <div>
                                        <div class="font-semibold text-white">{{ $business->business_name }}</div>
                                        <div class="text-sm text-slate-400">{{ $business->country }} • {{ $business->industry }} • {{ $business->email }}</div>
                                        @if ($business->review_notes)
                                            <p class="mt-1 text-sm text-slate-400">{{ $business->review_notes }}</p>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span @class([
                                            'rounded-full px-3 py-1 text-xs font-semibold',
                                            'bg-emerald-500/15 text-emerald-300' => $business->status === 'approved',
                                            'bg-amber-500/15 text-amber-300' => in_array($business->status, ['pending', 'under_review']),
                                            'bg-rose-500/15 text-rose-300' => in_array($business->status, ['rejected', 'suspended']),
                                        ])>
                                            {{ ucfirst(str_replace('_', ' ', $business->status)) }}
                                        </span>
                                    </div>
                                </div>

                                <div class="mt-3 flex flex-wrap gap-2">
                                    @if (in_array($business->status, ['pending', 'under_review']))
                                        <form method="POST" action="{{ route('admin.businesses.review', $business) }}">
                                            @csrf
                                            <input type="hidden" name="status" value="approved">
                                            <button type="submit" class="rounded-full bg-emerald-500 px-3 py-1 text-xs font-semibold text-slate-950 hover:bg-emerald-400">Approve</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.businesses.review', $business) }}">
                                            @csrf
                                            <input type="hidden" name="status" value="rejected">
                                            <button type="submit" class="rounded-full bg-rose-500/15 px-3 py-1 text-xs font-semibold text-rose-300 hover:bg-rose-500/25">Reject</button>
                                        </form>
                                    @elseif ($business->status === 'approved')
                                        <form method="POST" action="{{ route('admin.businesses.review', $business) }}">
                                            @csrf
                                            <input type="hidden" name="status" value="suspended">
                                            <button type="submit" class="rounded-full bg-rose-500/15 px-3 py-1 text-xs font-semibold text-rose-300 hover:bg-rose-500/25">Suspend</button>
                                        </form>
                                    @elseif (in_array($business->status, ['suspended', 'rejected']))
                                        <form method="POST" action="{{ route('admin.businesses.review', $business) }}">
                                            @csrf
                                            <input type="hidden" name="status" value="approved">
                                            <button type="submit" class="rounded-full bg-emerald-500 px-3 py-1 text-xs font-semibold text-slate-950 hover:bg-emerald-400">Reinstate</button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
