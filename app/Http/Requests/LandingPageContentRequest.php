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
            'hero_cta_text' => ['required', 'string', 'max:255'],
            'features_heading' => ['required', 'string', 'max:255'],
            'features_subtext' => ['required', 'string', 'max:500'],
            'features' => ['required', 'array', 'min:1', 'max:20'],
            'features.*.eyebrow' => ['nullable', 'string', 'max:255'],
            'features.*.title' => ['required', 'string', 'max:255'],
            'features.*.description' => ['required', 'string', 'max:500'],
            'requirements_heading' => ['required', 'string', 'max:255'],
            'requirements_subtext' => ['required', 'string', 'max:500'],
            'requirements' => ['required', 'array', 'min:1', 'max:20'],
            'requirements.*.title' => ['required', 'string', 'max:255'],
            'requirements.*.description' => ['required', 'string', 'max:500'],
            'requirements.*.type' => ['nullable', 'string', Rule::in(array_keys(LandingPageContent::REQUIREMENT_TYPE_OPTIONS))],
            'requirements.*.image_path' => ['nullable', 'string', 'max:255'],
            'requirements.*.image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
            'faq_heading' => ['required', 'string', 'max:255'],
            'faq_subtext' => ['required', 'string', 'max:500'],
            'faqs' => ['required', 'array', 'min:1', 'max:20'],
            'faqs.*.question' => ['required', 'string', 'max:255'],
            'faqs.*.answer' => ['required', 'string', 'max:1000'],
            'cta_banner_heading' => ['required', 'string', 'max:255'],
            'cta_banner_subtext' => ['required', 'string', 'max:500'],
            'gateways_heading' => ['required', 'string', 'max:255'],
            'gateways_subtext' => ['required', 'string', 'max:500'],
            'balances_heading' => ['required', 'string', 'max:255'],
            'balances_subtext' => ['required', 'string', 'max:500'],
            'balances_bullets' => ['required', 'array', 'min:1', 'max:20'],
            'balances_bullets.*' => ['required', 'string', 'max:255'],
            'balances_panel_rows' => ['required', 'array', 'min:1', 'max:20'],
            'balances_panel_rows.*.label' => ['required', 'string', 'max:255'],
            'balances_panel_rows.*.value' => ['required', 'string', 'max:255'],
            'payment_links_heading' => ['required', 'string', 'max:255'],
            'payment_links_subtext' => ['required', 'string', 'max:500'],
            'register_prompt_heading' => ['required', 'string', 'max:255'],
            'register_individual_title' => ['required', 'string', 'max:255'],
            'register_individual_description' => ['required', 'string', 'max:500'],
            'register_business_title' => ['required', 'string', 'max:255'],
            'register_business_description' => ['required', 'string', 'max:500'],
            'register_ngo_title' => ['required', 'string', 'max:255'],
            'register_ngo_description' => ['required', 'string', 'max:500'],
            'contact_location' => ['nullable', 'string', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'footer_tagline' => ['required', 'string', 'max:255'],
            'heading_sizes' => ['nullable', 'array'],
            'heading_sizes.*' => ['nullable', 'string', Rule::in(array_keys(LandingPageContent::HEADING_SIZES))],
            'section_heading_size_px' => ['required', 'integer', 'min:12', 'max:120'],
            'section_description_size_px' => ['required', 'integer', 'min:10', 'max:60'],
            'hero_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
            'how_it_works_heading' => ['required', 'string', 'max:255'],
            'how_it_works_steps' => ['required', 'array', 'min:1', 'max:20'],
            'how_it_works_steps.*.title' => ['required', 'string', 'max:255'],
            'how_it_works_steps.*.description' => ['required', 'string', 'max:500'],
            'how_it_works_cta_text' => ['required', 'string', 'max:255'],
            'how_it_works_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
            'payment_links_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
            'payment_logos' => ['required', 'array', 'min:1', 'max:20'],
            'payment_logos.*.label' => ['nullable', 'string', 'max:255'],
            'payment_logos.*.image_path' => ['nullable', 'string', 'max:255'],
            'payment_logos.*.image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
        ];
    }
}
