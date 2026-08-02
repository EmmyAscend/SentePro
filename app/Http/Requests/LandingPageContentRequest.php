<?php

namespace App\Http\Requests;

use App\Models\LandingPageContent;
use Illuminate\Foundation\Http\FormRequest;

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
            'features' => ['required', 'array', 'size:4'],
            'features.*.title' => ['required', 'string', 'max:255'],
            'features.*.description' => ['required', 'string', 'max:500'],
            'requirements' => ['required', 'array', 'size:3'],
            'requirements.*.title' => ['required', 'string', 'max:255'],
            'requirements.*.description' => ['required', 'string', 'max:500'],
            'faqs' => ['required', 'array', 'size:5'],
            'faqs.*.question' => ['required', 'string', 'max:255'],
            'faqs.*.answer' => ['required', 'string', 'max:1000'],
            'cta_banner_heading' => ['required', 'string', 'max:255'],
            'cta_banner_subtext' => ['required', 'string', 'max:500'],
            'hero_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
            'how_it_works_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
            'payment_links_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
            'payment_logos' => ['required', 'array', 'size:4'],
            'payment_logos.*.label' => ['required', 'string', 'max:255'],
            'payment_logos.*.image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
        ];
    }
}
