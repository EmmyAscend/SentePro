<?php

namespace Tests\Feature;

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
}
