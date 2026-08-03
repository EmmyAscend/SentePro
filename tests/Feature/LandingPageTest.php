<?php

namespace Tests\Feature;

use App\Models\LandingPageContent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_displays_sentepro_branding(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Collect Payments. Settle Faster. Grow Your Business.');
    }

    public function test_footer_credits_razertech_with_a_new_tab_link(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Powered by');
        $response->assertSee('href="https://razertechnology.com" target="_blank" rel="noopener noreferrer"', false);
        $response->assertSee('RAZERTECH');
    }

    public function test_footer_shows_copyright_before_the_razertech_credit(): void
    {
        $response = $this->get('/');
        $response->assertOk();

        $content = $response->getContent();
        $copyrightPos = strpos($content, 'All rights reserved.');
        $poweredByPos = strpos($content, 'Powered by');

        $this->assertNotFalse($copyrightPos);
        $this->assertNotFalse($poweredByPos);
        $this->assertTrue($copyrightPos < $poweredByPos);
    }

    public function test_footer_hides_contact_section_when_not_configured(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertDontSee('>Contact<', false);
    }

    public function test_hero_cta_defaults_to_start_free_onboarding(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Start free onboarding');
    }

    public function test_faq_section_defaults_to_frequently_asked_questions(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Frequently Asked Questions');
    }

    public function test_the_for_business_and_for_customers_cards_are_removed(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertDontSee('Run your payment operations from one dashboard');
        $response->assertDontSee('A fast, familiar checkout');
    }

    public function test_requirements_render_as_alternating_sections_with_register_links(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Who can use SentePro?');
        $response->assertSee(route('business.register', ['type' => 'ngo']), false);
        $response->assertSee(route('business.register', ['type' => 'business']), false);
        $response->assertSee(route('business.register', ['type' => 'individual']), false);
    }

    public function test_requirements_default_order_is_individual_business_ngo(): void
    {
        $response = $this->get('/');
        $response->assertOk();

        $content = $response->getContent();
        $individualPos = strpos($content, 'Individuals');
        $businessPos = strpos($content, 'Businesses');
        $ngoPos = strpos($content, 'NGOs');

        $this->assertNotFalse($individualPos);
        $this->assertNotFalse($businessPos);
        $this->assertNotFalse($ngoPos);
        $this->assertTrue($individualPos < $businessPos);
        $this->assertTrue($businessPos < $ngoPos);
    }

    public function test_requirements_and_features_no_longer_render_icon_circles(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertDontSee('h-10 w-10 items-center justify-center rounded-full bg-lime-400/10', false);
    }

    public function test_features_render_as_cards(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $html = $response->getContent();

        // 4 feature cards cycling through 3 pastel tones.
        $this->assertStringContainsString('rounded-[2rem] p-6 shadow-sm ring-1 ring-slate-900/5', $html);
        foreach (['bg-orange-50', 'bg-violet-50', 'bg-lime-50'] as $tone) {
            $this->assertStringContainsString($tone, $html);
        }
    }

    public function test_footer_wraps_powered_by_and_copyright_onto_separate_lines_on_mobile(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('mx-auto mt-10 flex max-w-6xl flex-col border-t border-white/10 px-4 pt-6 text-xs text-slate-500 sm:flex-row', false);
    }

    public function test_payment_links_spotlight_image_keeps_its_original_small_size(): void
    {
        LandingPageContent::current()->update(['payment_links_image_path' => 'landing-page/fake-payment-links.jpg']);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('aspect-square w-64 object-cover', false);
        $response->assertDontSee('aspect-square w-full object-cover', false);
    }

    public function test_hero_and_how_it_works_images_are_reduced_on_desktop(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        // Hero + how-it-works = 2 image boxes shrunk to 3/4 size. Requirements
        // images are exempt — see test_requirements_images_are_shown_in_full.
        $this->assertSame(2, substr_count($response->getContent(), 'lg:h-3/4 lg:w-3/4'));
    }

    public function test_requirements_images_are_shown_in_full(): void
    {
        $content = LandingPageContent::current();
        $requirements = $content->requirements;
        $requirements[0]['image_path'] = 'landing-page/fake-individual.jpg';
        $content->update(['requirements' => $requirements]);

        $response = $this->get('/');

        $response->assertOk();
        $html = $response->getContent();

        // Full-size box (not the 3/4-shrunk box Hero/how-it-works use) so
        // the image isn't shrunk down, and object-contain (not
        // object-cover) so nothing is cropped out of view.
        $this->assertSame(3, substr_count($html, 'rounded-[2rem] border border-slate-800 bg-slate-900 lg:h-full lg:w-full'));
        // Hero + how-it-works still use the 3/4-shrunk box; Requirements no longer does.
        $this->assertSame(1, substr_count($html, 'shadow-xl shadow-slate-900/10 lg:h-3/4 lg:w-3/4'));
        $this->assertSame(1, substr_count($html, 'rounded-[2rem] border border-slate-800 bg-slate-900 lg:h-3/4 lg:w-3/4'));
        $response->assertSee('aspect-[4/3] w-full object-contain lg:aspect-auto lg:h-full lg:w-full', false);
    }

    public function test_section_text_sizes_default_to_a_larger_desktop_size(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('--sh-desktop: 48px', false);
        $response->assertSee('--sd-desktop: 20px', false);
    }

    public function test_sections_are_separated_by_one_centimeter_of_space(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $content = $response->getContent();

        // One boundary per top-level section (payment logos, requirements,
        // features/balances group, payment links, how it works,
        // gateways/faq group, cta banner).
        $this->assertSame(7, substr_count($content, 'mt-[1cm]'));
        // Between each alternating requirement section, features→balances,
        // and gateways→faq, inside their shared wrappers.
        $this->assertSame(3, substr_count($content, 'space-y-[1cm]'));
    }

    public function test_requirements_payment_links_and_how_it_works_buttons_do_not_stretch_full_width_on_desktop(): void
    {
        $response = $this->get('/');

        $response->assertOk();

        // Each button sits inside a `lg:flex lg:flex-col` column, which
        // stretches children to full width by default; lg:self-start opts
        // the button back out to its natural (short) width. 3 requirement
        // register buttons + payment-links "Get started" + how-it-works CTA.
        $this->assertSame(5, substr_count($response->getContent(), 'lg:self-start'));
    }

    public function test_requirements_sections_are_more_compact(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('lg:min-h-[20rem]', false);
    }

    public function test_hero_adjacent_payment_logos_are_left_aligned_on_desktop(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $content = $response->getContent();
        $heroLogosSection = substr($content, 0, strpos($content, 'Who can use SentePro?'));

        // Still centered on mobile/tablet, left-aligned from lg: up.
        $this->assertStringContainsString('justify-center', $heroLogosSection);
        $this->assertStringContainsString('lg:justify-start', $heroLogosSection);
    }

    public function test_supported_payment_logos_are_reduced_on_desktop(): void
    {
        $content = LandingPageContent::current();
        $logos = $content->payment_logos;
        $logos[0]['image_path'] = 'landing-page/fake-visa-logo.jpg';
        $content->update(['payment_logos' => $logos]);

        $response = $this->get('/');

        $response->assertOk();
        // Placeholder text logos (MTN, Airtel).
        $response->assertSee('lg:text-6xl', false);
        // Uploaded image logo (Visa, forced above).
        $response->assertSee('lg:h-20 lg:max-w-[20rem]', false);
        // Mastercard's two-circle mark and its label.
        $response->assertSee('lg:h-20 lg:w-28', false);
        $response->assertSee('lg:h-20 lg:w-20', false);
        $response->assertSee('lg:text-4xl', false);
    }

    public function test_ngo_register_button_uses_a_short_label(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Register as NGO');
        $response->assertDontSee('Register as Non-Profit Organisation');
    }

    public function test_the_page_uses_a_light_theme_with_a_dark_nav_no_longer_present(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $html = $response->getContent();

        $this->assertStringContainsString('bg-white text-slate-900', $html);
        $this->assertStringContainsString('bg-white/90', $html);
        $this->assertStringNotContainsString('bg-slate-950/80', $html);
    }

    public function test_the_footer_has_its_own_explicit_dark_background(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('rounded-t-[2rem] bg-slate-900 py-12 text-white', false);
    }

    public function test_ctas_are_pill_shaped(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $this->assertGreaterThan(0, substr_count($response->getContent(), 'rounded-full bg-lime-400'));
    }

    public function test_the_dark_panels_are_contained_cards_not_full_bleed(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $html = $response->getContent();

        // Payment-links spotlight, how-it-works, and the CTA banner each now
        // sit inside a max-w-6xl gutter with rounded corners, rather than
        // bleeding edge-to-edge across the full viewport width. (The payment
        // logos strip shares this exact wrapper class too — 4 total.)
        $this->assertSame(4, substr_count($html, 'mx-auto mt-[1cm] max-w-6xl px-4 sm:px-6 lg:px-8'));
        $this->assertStringContainsString('overflow-hidden rounded-[2rem] lg:grid lg:min-h-[30rem]', $html);
        $this->assertStringContainsString('relative overflow-hidden rounded-[2rem] bg-slate-900 py-20', $html);
    }
}
