<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-2xl font-bold text-white">{{ $article->title }}</h2>
            <a href="{{ route('knowledge-base.index') }}" class="rounded-full border border-white/10 px-4 py-2 text-sm font-semibold text-slate-300 hover:bg-white/5">Back to Knowledge Base</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-2xl bg-slate-900 p-6 shadow-sm ring-1 ring-white/10">
                <p class="whitespace-pre-line text-sm text-slate-300">{{ $article->body }}</p>
            </div>
        </div>
    </div>
</x-app-layout>
