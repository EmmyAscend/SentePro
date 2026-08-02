<?php

namespace App\Http\Controllers;

use App\Http\Requests\LandingPageContentRequest;
use App\Models\LandingPageContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

        $data = collect($validated)->except([...array_keys(self::IMAGE_COLUMNS), 'payment_logos', 'requirements'])->toArray();

        foreach (self::IMAGE_COLUMNS as $field => $column) {
            if ($request->hasFile($field)) {
                if ($content->{$column}) {
                    Storage::disk('public')->delete($content->{$column});
                }
                $data[$column] = $request->file($field)->store('landing-page', 'public');
            }
        }

        $data['payment_logos'] = collect($validated['payment_logos'])->map(function (array $logo, int $i) use ($request) {
            return [
                'label' => $logo['label'] ?? null,
                'image_path' => $this->resolveItemImage($logo, 'payment_logos', $i, $request),
            ];
        })->all();

        $data['requirements'] = collect($validated['requirements'])->map(function (array $requirement, int $i) use ($request) {
            $requirement['image_path'] = $this->resolveItemImage($requirement, 'requirements', $i, $request);
            unset($requirement['image']);

            return $requirement;
        })->all();

        $content->update($data);

        return redirect()->route('admin.landing-page.edit')->with('status', 'Landing page content updated.');
    }

    /**
     * Resolve the image path a dynamic repeater item (payment logo,
     * requirement) should be saved with: the current path it carried
     * forward as a hidden field, replaced with a freshly uploaded file if
     * one was submitted for that item's index (deleting the old file so
     * storage doesn't accumulate orphans).
     */
    private function resolveItemImage(array $item, string $field, int $index, Request $request): ?string
    {
        $imagePath = $item['image_path'] ?? null;

        if ($request->hasFile("{$field}.{$index}.image")) {
            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }
            $imagePath = $request->file("{$field}.{$index}.image")->store('landing-page', 'public');
        }

        return $imagePath;
    }
}
