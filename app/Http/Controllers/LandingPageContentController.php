<?php

namespace App\Http\Controllers;

use App\Http\Requests\LandingPageContentRequest;
use App\Models\LandingPageContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LandingPageContentController extends Controller
{
    public function edit(): View
    {
        $this->authorize('manage', LandingPageContent::class);

        $content = LandingPageContent::current();

        return view('admin.landing-page', compact('content'));
    }

    public function update(LandingPageContentRequest $request): RedirectResponse
    {
        LandingPageContent::current()->update($request->validated());

        return redirect()->route('admin.landing-page.edit')->with('status', 'Landing page content updated.');
    }
}
