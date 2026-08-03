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
        $response->assertSee('rounded-3xl bg-slate-900 p-6 ring-1 ring-white/10', false);
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

    public function test_hero_requirements_and_how_it_works_images_are_reduced_on_desktop(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        // Hero + 3 requirement sections + how-it-works = 5 image boxes shrunk to 3/4 size.
        $response->assertSee('lg:h-3/4 lg:w-3/4', false);
    }

    public function test_section_text_sizes_default_to_a_larger_desktop_size(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('--sh-desktop: 48px', false);
        $response->assertSee('--sd-desktop: 20px', false);
    }
}
