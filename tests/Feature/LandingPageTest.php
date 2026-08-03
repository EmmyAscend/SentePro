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

    public function test_features_render_as_a_hover_highlighted_services_list(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $html = $response->getContent();

        // 4 feature rows, Alpine-driven active-row highlight, no card grid.
        $this->assertSame(4, substr_count($html, 'border-b border-slate-200 px-4 py-5'));
        $this->assertStringContainsString('@mouseenter="active =', $html);
        $this->assertStringNotContainsString('rounded-3xl bg-slate-900 p-6', $html);
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

    public function test_how_it_works_image_is_reduced_on_desktop(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        // Hero is now a full-bleed background image with no box to shrink;
        // only how-it-works still uses the 3/4-shrunk box treatment.
        $this->assertSame(1, substr_count($response->getContent(), 'lg:h-3/4 lg:w-3/4'));
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

        // Plain, unshrunk box (not the 3/4-shrunk box how-it-works uses),
        // rendered once per requirement regardless of whether it ends up
        // showing a real photo or the illustration fallback. Matched with
        // the closing quote so how-it-works' longer class list (which
        // shares this same prefix, plus lg:h-3/4 lg:w-3/4) isn't counted.
        $this->assertSame(3, substr_count($html, 'overflow-hidden rounded-[2rem] border border-slate-800 bg-slate-900"'));
        // object-contain (not object-cover) on the one item that has a real
        // photo, so nothing is cropped out of view.
        $response->assertSee('alt="Individuals" class="aspect-[4/3] w-full object-contain"', false);
    }

    public function test_section_text_sizes_default_to_a_larger_desktop_size(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('--sh-desktop: 48px', false);
        $response->assertSee('--sd-desktop: 20px', false);
    }

    public function test_hero_payment_links_and_how_it_works_buttons_do_not_stretch_full_width(): void
    {
        $response = $this->get('/');

        $response->assertOk();

        // Hero's badge/CTA column and the payment-links/how-it-works text
        // columns are all `flex flex-col` containers, which stretch children
        // to full width by default; w-fit opts the button back out to its
        // natural (short) width. Hero CTA + Features "Join us today" +
        // payment-links "Get started" + how-it-works CTA.
        $this->assertSame(4, substr_count($response->getContent(), 'inline-flex w-fit'));
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

        $this->assertStringContainsString('bg-stone-50 text-slate-900', $html);
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

    public function test_the_dark_panels_and_cta_banner_span_the_full_width(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $html = $response->getContent();

        // Matches the SportVibe reference's own rhythm: full-width section
        // backgrounds with individual floating cards inside, not Fynlo's
        // "everything is an inset rounded card with page gutters" pattern.
        $this->assertStringContainsString('bg-slate-900 lg:grid lg:min-h-[28rem] lg:grid-cols-2 lg:items-stretch', $html);
        $this->assertStringNotContainsString('mx-auto mt-[1cm] max-w-6xl', $html);
        $this->assertStringNotContainsString('overflow-hidden rounded-[2rem] lg:grid', $html);
    }

    public function test_no_fabricated_stats_or_team_photos_appear(): void
    {
        $response = $this->get('/');

        $response->assertOk();

        // The proven-results panel and about-section stat pill must use real,
        // already-existing CMS copy, never an invented percentage/count, and
        // there's no staff/coach data anywhere in this app to show a team row.
        $response->assertDontSee('100K+');
        $response->assertDontSee('30%');
        $response->assertDontSee('+40%');
        $response->assertDontSee('5:1');
        $response->assertDontSee('MEET OUR EXPERT TEAM', false);
        $response->assertSee('Request a settlement the moment funds are available');
        $response->assertSee('font-display text-3xl text-slate-900">/2</p>', false);
        $response->assertSee('Gateways supported');
    }

    public function test_headings_use_the_new_display_font(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $this->assertGreaterThan(5, substr_count($response->getContent(), 'font-display'));
    }

    public function test_hero_badge_reuses_real_hero_badge_text_not_a_fake_stat(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('/East Africa payment infrastructure', false);
    }
}
