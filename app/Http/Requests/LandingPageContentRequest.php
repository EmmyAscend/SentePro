<?php

namespace App\Http\Requests;

use App\Models\LandingPageContent;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LandingPageContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage', LandingPageContent::class);
    }

    public function rules(): array
    {
        return [
            'hero_badge_text' => ['required', 'string', 'max:255'],
            'hero_headline' => ['required', 'string', 'max:255'],
            'hero_subtext' => ['required', 'string', 'max:1000'],
            'features' => ['required', 'array', 'min:1', 'max:20'],
            'features.*.title' => ['required', 'string', 'max:255'],
            'features.*.description' => ['required', 'string', 'max:500'],
            'features.*.icon' => ['required', 'string', Rule::in(LandingPageContent::ICON_OPTIONS)],
            'requirements' => ['required', 'array', 'min:1', 'max:20'],
            'requirements.*.title' => ['required', 'string', 'max:255'],
            'requirements.*.description' => ['required', 'string', 'max:500'],
            'requirements.*.icon' => ['required', 'string', Rule::in(LandingPageContent::ICON_OPTIONS)],
            'faqs' => ['required', 'array', 'min:1', 'max:20'],
            'faqs.*.question' => ['required', 'string', 'max:255'],
            'faqs.*.answer' => ['required', 'string', 'max:1000'],
            'cta_banner_heading' => ['required', 'string', 'max:255'],
            'cta_banner_subtext' => ['required', 'string', 'max:500'],
            'contact_location' => ['nullable', 'string', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'footer_tagline' => ['required', 'string', 'max:255'],
            'hero_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
            'how_it_works_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
            'payment_links_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
            'payment_logos' => ['required', 'array', 'min:1', 'max:20'],
            'payment_logos.*.label' => ['required', 'string', 'max:255'],
            'payment_logos.*.image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
        ];
    }
}
