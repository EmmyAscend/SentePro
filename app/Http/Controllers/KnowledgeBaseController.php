<?php

namespace App\Http\Controllers;

use App\Http\Requests\KnowledgeBaseArticleRequest;
use App\Models\KnowledgeBaseArticle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KnowledgeBaseController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', KnowledgeBaseArticle::class);

        $articles = KnowledgeBaseArticle::query()
            ->when(! $request->user()->isSuperAdmin(), fn ($query) => $query->where('status', 'published'))
            ->latest()
            ->get();

        return view('knowledge-base.index', compact('articles'));
    }

    public function show(KnowledgeBaseArticle $article): View
    {
        $this->authorize('view', $article);

        return view('knowledge-base.show', compact('article'));
    }

    public function store(KnowledgeBaseArticleRequest $request): RedirectResponse
    {
        KnowledgeBaseArticle::create([...$request->validated(), 'created_by' => $request->user()->id]);

        return redirect()->route('knowledge-base.index')->with('status', 'Article created.');
    }

    public function update(KnowledgeBaseArticleRequest $request, KnowledgeBaseArticle $article): RedirectResponse
    {
        $article->update($request->validated());

        return redirect()->route('knowledge-base.index')->with('status', 'Article updated.');
    }
}
