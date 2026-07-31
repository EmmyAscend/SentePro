<?php

namespace Tests\Feature;

use Tests\TestCase;

class LandingPageTest extends TestCase
{
    public function test_home_page_displays_sentepro_branding(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Collect Payments. Settle Faster. Grow Your Business.');
    }
}
