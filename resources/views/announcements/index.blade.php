<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-white">Announcements</h2>
            <p class="text-sm text-slate-400">Platform-wide notices from the SentePro team</p>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 space-y-6">
            @can('manage', \App\Models\Announcement::class)
                <div class="rounded-2xl bg-slate-900 p-6 shadow-sm ring-1 ring-white/10">
                    <h3 class="text-lg font-semibold text-white">Add announcement</h3>
                    <form method="POST" action="{{ route('announcements.store') }}" class="mt-4 grid gap-4 md:grid-cols-2">
                        @csrf
                        <label class="flex flex-col gap-1 text-sm md:col-span-2">
                            <span class="font-medium text-slate-300">Title</span>
                            <input type="text" name="title" class="rounded-xl border border-white/10 bg-slate-950 text-white px-3 py-2" required>
                        </label>
                        <label class="flex flex-col gap-1 text-sm md:col-span-2">
                            <span class="font-medium text-slate-300">Body</span>
                            <textarea name="body" rows="4" class="rounded-xl border border-white/10 bg-slate-950 text-white px-3 py-2" required></textarea>
                        </label>
                        <label class="flex flex-col gap-1 text-sm">
                            <span class="font-medium text-slate-300">Status</span>
                            <select name="status" class="rounded-xl border border-white/10 bg-slate-950 text-white px-3 py-2">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </label>
                        <div class="flex items-end">
                            <button type="submit" class="rounded-xl bg-lime-400 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-lime-300">Add announcement</button>
                        </div>
                    </form>
                </div>
            @endcan

            <div class="space-y-4">
                @forelse ($announcements as $announcement)
                    <div x-data="{ editing: false }" class="rounded-2xl bg-slate-900 p-6 shadow-sm ring-1 ring-white/10">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p class="font-semibold text-white">{{ $announcement->title }}</p>
                                <p class="mt-1 whitespace-pre-line text-sm text-slate-300">{{ $announcement->body }}</p>
                                <p class="mt-2 text-xs text-slate-400">{{ $announcement->created_at->format('d M Y, H:i') }}</p>
                            </div>
                            @can('manage', \App\Models\Announcement::class)
                                <div class="flex shrink-0 items-center gap-2">
                                    <span class="rounded-full {{ $announcement->status === 'active' ? 'bg-emerald-500/15 text-emerald-300' : 'bg-slate-500/15 text-slate-300' }} px-3 py-1 text-xs font-semibold">
                                        {{ strtoupper($announcement->status) }}
                                    </span>
                                    <button type="button" @click="editing = !editing" class="rounded-full border border-white/10 px-3 py-1 text-xs font-semibold text-slate-300 hover:bg-white/5">
                                        Edit
                                    </button>
                                </div>
                            @endcan
                        </div>

                        @can('manage', \App\Models\Announcement::class)
                            <form x-show="editing" x-cloak method="POST" action="{{ route('announcements.update', $announcement) }}" class="mt-4 grid gap-4 border-t border-white/10 pt-4 md:grid-cols-2">
                                @csrf
                                @method('PUT')
                                <label class="flex flex-col gap-1 text-sm md:col-span-2">
                                    <span class="font-medium text-slate-300">Title</span>
                                    <input type="text" name="title" value="{{ $announcement->title }}" class="rounded-xl border border-white/10 bg-slate-950 text-white px-3 py-2" required>
                                </label>
                                <label class="flex flex-col gap-1 text-sm md:col-span-2">
                                    <span class="font-medium text-slate-300">Body</span>
                                    <textarea name="body" rows="4" class="rounded-xl border border-white/10 bg-slate-950 text-white px-3 py-2" required>{{ $announcement->body }}</textarea>
                                </label>
                                <label class="flex flex-col gap-1 text-sm">
                                    <span class="font-medium text-slate-300">Status</span>
                                    <select name="status" class="rounded-xl border border-white/10 bg-slate-950 text-white px-3 py-2">
                                        <option value="active" @selected($announcement->status === 'active')>Active</option>
                                        <option value="inactive" @selected($announcement->status === 'inactive')>Inactive</option>
                                    </select>
                                </label>
                                <div class="flex items-end">
                                    <button type="submit" class="rounded-xl bg-lime-400 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-lime-300">Save changes</button>
                                </div>
                            </form>
                        @endcan
                    </div>
                @empty
                    <p class="text-slate-400">No announcements right now.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
