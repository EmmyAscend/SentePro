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
}
