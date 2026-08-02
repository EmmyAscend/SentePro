<?php

namespace Tests\Feature;

use App\Models\LandingPageContent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingPageContentManagementTest extends TestCase
{
    use RefreshDatabase;

    private function validPayload(): array
    {
        return [
            'hero_badge_text' => 'Updated badge',
            'hero_headline' => 'Updated headline',
            'hero_subtext' => 'Updated subtext',
            'stat_1_label' => 'Stat one',
            'stat_1_value' => '10%',
            'stat_2_label' => 'Stat two',
            'stat_2_value' => '5',
            'features' => array_fill(0, 4, ['title' => 'Feature title', 'description' => 'Feature description']),
            'faqs' => array_fill(0, 5, ['question' => 'A question?', 'answer' => 'An answer.']),
            'cta_banner_heading' => 'Updated banner heading',
            'cta_banner_subtext' => 'Updated banner subtext',
        ];
    }

    public function test_super_admin_can_view_the_landing_page_editor(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($superAdmin)->get('/admin/landing-page');

        $response->assertOk();
        $response->assertSee('Landing Page');
    }

    public function test_super_admin_can_update_the_landing_page_content(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($superAdmin)->put('/admin/landing-page', $this->validPayload());

        $response->assertRedirect(route('admin.landing-page.edit'));
        $this->assertSame('Updated headline', LandingPageContent::current()->hero_headline);

        $home = $this->get('/');
        $home->assertSee('Updated headline');
    }

    public function test_business_admin_cannot_view_or_update_the_landing_page_editor(): void
    {
        $businessAdmin = User::factory()->businessAdmin()->create();

        $this->actingAs($businessAdmin)->get('/admin/landing-page')->assertForbidden();
        $this->actingAs($businessAdmin)->put('/admin/landing-page', $this->validPayload())->assertForbidden();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/admin/landing-page')->assertRedirect('/login');
    }
}
