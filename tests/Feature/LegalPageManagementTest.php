<?php

namespace Tests\Feature;

use App\Models\LegalPage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LegalPageManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_each_legal_page_is_publicly_viewable(): void
    {
        foreach (LegalPage::SLUGS as $slug) {
            $response = $this->get("/legal/{$slug}");

            $response->assertOk();
        }
    }

    public function test_an_unknown_legal_slug_is_not_found(): void
    {
        $this->get('/legal/something-else')->assertNotFound();
    }

    public function test_super_admin_can_view_and_update_a_legal_page(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $this->actingAs($superAdmin)->get('/admin/legal-pages')->assertOk();

        $response = $this->actingAs($superAdmin)->put('/admin/legal-pages/privacy-policy', [
            'title' => 'Updated Privacy Policy',
            'body' => 'Updated body text.',
        ]);

        $response->assertRedirect(route('admin.legal-pages'));
        $this->assertSame('Updated Privacy Policy', LegalPage::bySlug('privacy-policy')->title);

        $public = $this->get('/legal/privacy-policy');
        $public->assertSee('Updated Privacy Policy');
        $public->assertSee('Updated body text.');
    }

    public function test_business_admin_cannot_view_or_update_legal_pages(): void
    {
        LegalPage::bySlug('privacy-policy');

        $businessAdmin = User::factory()->businessAdmin()->create();

        $this->actingAs($businessAdmin)->get('/admin/legal-pages')->assertForbidden();
        $this->actingAs($businessAdmin)->put('/admin/legal-pages/privacy-policy', [
            'title' => 'Nope',
            'body' => 'Nope',
        ])->assertForbidden();
    }

    public function test_guest_is_redirected_to_login_for_the_admin_screen(): void
    {
        $this->get('/admin/legal-pages')->assertRedirect('/login');
    }
}
