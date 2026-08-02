<x-public-layout :title="$page->title.' | SentePro'">
    <div class="px-4 py-10 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl rounded-3xl border border-white/10 bg-white/5 p-6 shadow-2xl shadow-slate-950/40 sm:p-8">
            <h1 class="text-[clamp(1.25rem,4.5vw,2.25rem)] font-bold">{{ $page->title }}</h1>
            <p class="mt-1 text-sm text-slate-400">Last updated {{ $page->updated_at->format('F j, Y') }}</p>
            <div class="mt-6 whitespace-pre-line text-sm leading-relaxed text-slate-300">{{ $page->body }}</div>
        </div>
    </div>
</x-public-layout>
