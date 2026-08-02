<?php

namespace App\Http\Controllers;

use App\Http\Requests\LandingPageContentRequest;
use App\Models\LandingPageContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class LandingPageContentController extends Controller
{
    /**
     * Single-image fields that store a path directly on the model, mapped
     * from their upload field name to the column that holds the path.
     */
    private const IMAGE_COLUMNS = [
        'hero_image' => 'hero_image_path',
        'how_it_works_image' => 'how_it_works_image_path',
        'payment_links_image' => 'payment_links_image_path',
    ];

    public function edit(): View
    {
        $this->authorize('manage', LandingPageContent::class);

        $content = LandingPageContent::current();

        return view('admin.landing-page', compact('content'));
    }

    public function update(LandingPageContentRequest $request): RedirectResponse
    {
        $content = LandingPageContent::current();
        $validated = $request->validated();

        $data = collect($validated)->except([...array_keys(self::IMAGE_COLUMNS), 'payment_logos'])->toArray();

        foreach (self::IMAGE_COLUMNS as $field => $column) {
            if ($request->hasFile($field)) {
                if ($content->{$column}) {
                    Storage::disk('public')->delete($content->{$column});
                }
                $data[$column] = $request->file($field)->store('landing-page', 'public');
            }
        }

        $data['payment_logos'] = collect($validated['payment_logos'])->map(function (array $logo, int $i) use ($request) {
            $imagePath = $logo['image_path'] ?? null;

            if ($request->hasFile("payment_logos.{$i}.image")) {
                if ($imagePath) {
                    Storage::disk('public')->delete($imagePath);
                }
                $imagePath = $request->file("payment_logos.{$i}.image")->store('landing-page', 'public');
            }

            return ['label' => $logo['label'] ?? null, 'image_path' => $imagePath];
        })->all();

        $content->update($data);

        return redirect()->route('admin.landing-page.edit')->with('status', 'Landing page content updated.');
    }
}
