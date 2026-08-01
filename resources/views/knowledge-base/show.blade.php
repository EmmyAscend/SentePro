<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-2xl font-bold text-slate-900">{{ $article->title }}</h2>
            <a href="{{ route('knowledge-base.index') }}" class="rounded-full border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Back to Knowledge Base</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                <p class="whitespace-pre-line text-sm text-slate-700">{{ $article->body }}</p>
            </div>
        </div>
    </div>
</x-app-layout>
