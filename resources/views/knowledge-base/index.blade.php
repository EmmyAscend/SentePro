<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-slate-900">Knowledge Base</h2>
            <p class="text-sm text-slate-500">Help articles and guides for using SentePro</p>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 space-y-6">
            @can('manage', \App\Models\KnowledgeBaseArticle::class)
                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                    <h3 class="text-lg font-semibold text-slate-900">Add article</h3>
                    <form method="POST" action="{{ route('knowledge-base.store') }}" class="mt-4 grid gap-4 md:grid-cols-2">
                        @csrf
                        <label class="flex flex-col gap-1 text-sm md:col-span-2">
                            <span class="font-medium text-slate-700">Title</span>
                            <input type="text" name="title" class="rounded-xl border border-slate-300 px-3 py-2" required>
                        </label>
                        <label class="flex flex-col gap-1 text-sm md:col-span-2">
                            <span class="font-medium text-slate-700">Body</span>
                            <textarea name="body" rows="6" class="rounded-xl border border-slate-300 px-3 py-2" required></textarea>
                        </label>
                        <label class="flex flex-col gap-1 text-sm">
                            <span class="font-medium text-slate-700">Status</span>
                            <select name="status" class="rounded-xl border border-slate-300 px-3 py-2">
                                <option value="draft">Draft</option>
                                <option value="published">Published</option>
                            </select>
                        </label>
                        <div class="flex items-end md:col-span-1">
                            <button type="submit" class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Add article</button>
                        </div>
                    </form>
                </div>
            @endcan

            <div class="space-y-4">
                @forelse ($articles as $article)
                    <div x-data="{ editing: false }" class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <a href="{{ route('knowledge-base.show', $article) }}" class="font-semibold text-slate-900 hover:underline">{{ $article->title }}</a>
                                <p class="mt-1 text-sm text-slate-500">{{ \Illuminate\Support\Str::limit(strip_tags($article->body), 140) }}</p>
                            </div>
                            <div class="flex shrink-0 items-center gap-2">
                                @can('manage', \App\Models\KnowledgeBaseArticle::class)
                                    <span class="rounded-full {{ $article->status === 'published' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }} px-3 py-1 text-xs font-semibold">
                                        {{ strtoupper($article->status) }}
                                    </span>
                                    <button type="button" @click="editing = !editing" class="rounded-full border border-slate-300 px-3 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                                        Edit
                                    </button>
                                @endcan
                            </div>
                        </div>

                        @can('manage', \App\Models\KnowledgeBaseArticle::class)
                            <form x-show="editing" x-cloak method="POST" action="{{ route('knowledge-base.update', $article) }}" class="mt-4 grid gap-4 border-t border-slate-100 pt-4 md:grid-cols-2">
                                @csrf
                                @method('PUT')
                                <label class="flex flex-col gap-1 text-sm md:col-span-2">
                                    <span class="font-medium text-slate-700">Title</span>
                                    <input type="text" name="title" value="{{ $article->title }}" class="rounded-xl border border-slate-300 px-3 py-2" required>
                                </label>
                                <label class="flex flex-col gap-1 text-sm md:col-span-2">
                                    <span class="font-medium text-slate-700">Body</span>
                                    <textarea name="body" rows="6" class="rounded-xl border border-slate-300 px-3 py-2" required>{{ $article->body }}</textarea>
                                </label>
                                <label class="flex flex-col gap-1 text-sm">
                                    <span class="font-medium text-slate-700">Status</span>
                                    <select name="status" class="rounded-xl border border-slate-300 px-3 py-2">
                                        <option value="draft" @selected($article->status === 'draft')>Draft</option>
                                        <option value="published" @selected($article->status === 'published')>Published</option>
                                    </select>
                                </label>
                                <div class="flex items-end">
                                    <button type="submit" class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Save changes</button>
                                </div>
                            </form>
                        @endcan
                    </div>
                @empty
                    <p class="text-slate-600">No articles published yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
