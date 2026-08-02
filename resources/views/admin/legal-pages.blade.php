<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-white">Legal Pages</h2>
            <p class="text-sm text-slate-400">Edit the Privacy Policy, Terms and Conditions, and Refund Policy shown in the site footer</p>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <p class="rounded-xl bg-emerald-500/15 px-4 py-3 text-sm font-medium text-emerald-300">{{ session('status') }}</p>
            @endif

            @foreach ($pages as $page)
                <div x-data="{ editing: false }" class="rounded-2xl bg-slate-900 p-6 shadow-sm ring-1 ring-white/10">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <a href="{{ route('legal.show', $page->slug) }}" target="_blank" class="font-semibold text-white hover:underline">{{ $page->title }}</a>
                            <p class="mt-1 text-sm text-slate-400">{{ \Illuminate\Support\Str::limit($page->body, 140) }}</p>
                        </div>
                        <button type="button" @click="editing = !editing" class="shrink-0 rounded-full border border-white/10 px-3 py-1 text-xs font-semibold text-slate-300 hover:bg-white/5">
                            Edit
                        </button>
                    </div>

                    <form x-show="editing" x-cloak method="POST" action="{{ route('admin.legal-pages.update', $page) }}" class="mt-4 space-y-4 border-t border-white/10 pt-4">
                        @csrf
                        @method('PUT')
                        <label class="flex flex-col gap-1 text-sm">
                            <span class="font-medium text-slate-300">Title</span>
                            <input type="text" name="title" value="{{ $page->title }}" class="rounded-xl border border-white/10 bg-slate-950 text-white px-3 py-2" required>
                        </label>
                        <label class="flex flex-col gap-1 text-sm">
                            <span class="font-medium text-slate-300">Body</span>
                            <textarea name="body" rows="14" class="rounded-xl border border-white/10 bg-slate-950 text-white px-3 py-2 font-mono text-xs" required>{{ $page->body }}</textarea>
                        </label>
                        <button type="submit" class="rounded-xl bg-lime-400 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-lime-300">Save changes</button>
                    </form>
                </div>
            @endforeach
        </div>
    </div>
</x-app-layout>
