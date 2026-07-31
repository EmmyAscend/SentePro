<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-slate-900">Audit Logs</h2>
            <p class="text-sm text-slate-500">Who did what, when, and from where — the most recent 200 events</p>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                <form method="GET" action="{{ route('admin.audit-logs') }}" class="grid gap-4 md:grid-cols-4">
                    <label class="flex flex-col gap-1 text-sm">
                        <span class="font-medium text-slate-700">Business</span>
                        <select name="business_id" class="rounded-xl border border-slate-300 px-3 py-2">
                            <option value="">All businesses</option>
                            @foreach ($businesses as $business)
                                <option value="{{ $business->id }}" @selected(request('business_id') == $business->id)>{{ $business->business_name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="flex flex-col gap-1 text-sm">
                        <span class="font-medium text-slate-700">Action</span>
                        <select name="action" class="rounded-xl border border-slate-300 px-3 py-2">
                            <option value="">All actions</option>
                            @foreach ($actions as $action)
                                <option value="{{ $action }}" @selected(request('action') === $action)>{{ $action }}</option>
                            @endforeach
                        </select>
                    </label>
                    <div class="flex items-end gap-2">
                        <button type="submit" class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Filter</button>
                        <a href="{{ route('admin.audit-logs') }}" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Clear</a>
                    </div>
                </form>
            </div>

            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                <h3 class="text-lg font-semibold text-slate-900">Events</h3>
                @if ($logs->isEmpty())
                    <p class="mt-4 text-slate-600">No audit log entries match these filters.</p>
                @else
                    <div class="mt-4 space-y-3">
                        @foreach ($logs as $log)
                            <div class="rounded-xl bg-slate-50 px-4 py-3">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <div>
                                        <span class="font-semibold text-slate-900">{{ $log->action }}</span>
                                        <span class="text-sm text-slate-500">
                                            by {{ $log->user?->name ?? 'System' }}
                                            @if ($log->subject_type)
                                                on {{ class_basename($log->subject_type) }} #{{ $log->subject_id }}
                                            @endif
                                        </span>
                                    </div>
                                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                                        {{ $log->business?->business_name ?? '— platform —' }}
                                    </span>
                                </div>
                                <div class="mt-1 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-500">
                                    <span>{{ $log->created_at->format('d M Y, H:i:s') }}</span>
                                    <span>IP {{ $log->ip_address ?? 'unknown' }}</span>
                                    @if (! empty($log->changes))
                                        <span>{{ json_encode($log->changes) }}</span>
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
