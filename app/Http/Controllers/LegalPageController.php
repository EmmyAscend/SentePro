<?php

namespace App\Http\Controllers;

use App\Http\Requests\LegalPageRequest;
use App\Models\LegalPage;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LegalPageController extends Controller
{
    public function show(string $slug): View
    {
        $page = LegalPage::bySlug($slug);

        return view('legal.show', compact('page'));
    }

    public function index(): View
    {
        $this->authorize('manage', LegalPage::class);

        $pages = collect(LegalPage::SLUGS)->map(fn (string $slug) => LegalPage::bySlug($slug));

        return view('admin.legal-pages', compact('pages'));
    }

    public function update(LegalPageRequest $request, LegalPage $legalPage): RedirectResponse
    {
        $legalPage->update($request->validated());

        return redirect()->route('admin.legal-pages')->with('status', 'Legal page updated.');
    }
}
