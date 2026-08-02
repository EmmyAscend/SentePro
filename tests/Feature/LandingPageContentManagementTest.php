<?php

namespace Tests\Feature;

use App\Models\LandingPageContent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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
            'payment_logos' => array_fill(0, 4, ['label' => 'Visa']),
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

    public function test_super_admin_can_upload_a_hero_image(): void
    {
        Storage::fake('public');
        $superAdmin = User::factory()->superAdmin()->create();

        $payload = array_merge($this->validPayload(), [
            'hero_image' => UploadedFile::fake()->image('hero.jpg'),
        ]);

        $this->actingAs($superAdmin)->put('/admin/landing-page', $payload)->assertRedirect();

        $path = LandingPageContent::current()->hero_image_path;
        $this->assertNotNull($path);
        Storage::disk('public')->assertExists($path);

        $this->get('/')->assertSee(Storage::url($path), false);
    }

    public function test_re_saving_without_a_new_file_keeps_the_existing_image(): void
    {
        Storage::fake('public');
        $superAdmin = User::factory()->superAdmin()->create();

        $withImage = array_merge($this->validPayload(), [
            'hero_image' => UploadedFile::fake()->image('hero.jpg'),
        ]);
        $this->actingAs($superAdmin)->put('/admin/landing-page', $withImage);
        $originalPath = LandingPageContent::current()->hero_image_path;

        $this->actingAs($superAdmin)->put('/admin/landing-page', $this->validPayload());

        $this->assertSame($originalPath, LandingPageContent::current()->hero_image_path);
        Storage::disk('public')->assertExists($originalPath);
    }

    public function test_uploading_a_replacement_image_deletes_the_old_one(): void
    {
        Storage::fake('public');
        $superAdmin = User::factory()->superAdmin()->create();

        $first = array_merge($this->validPayload(), [
            'hero_image' => UploadedFile::fake()->image('hero.jpg'),
        ]);
        $this->actingAs($superAdmin)->put('/admin/landing-page', $first);
        $originalPath = LandingPageContent::current()->hero_image_path;

        $second = array_merge($this->validPayload(), [
            'hero_image' => UploadedFile::fake()->image('hero-2.jpg'),
        ]);
        $this->actingAs($superAdmin)->put('/admin/landing-page', $second);
        $newPath = LandingPageContent::current()->hero_image_path;

        $this->assertNotSame($originalPath, $newPath);
        Storage::disk('public')->assertMissing($originalPath);
        Storage::disk('public')->assertExists($newPath);
    }

    public function test_super_admin_can_upload_a_payment_logo_image(): void
    {
        Storage::fake('public');
        $superAdmin = User::factory()->superAdmin()->create();

        $logos = array_fill(0, 4, ['label' => 'Visa']);
        $logos[0]['image'] = UploadedFile::fake()->image('visa.png');

        $payload = array_merge($this->validPayload(), ['payment_logos' => $logos]);

        $this->actingAs($superAdmin)->put('/admin/landing-page', $payload)->assertRedirect();

        $path = LandingPageContent::current()->payment_logos[0]['image_path'];
        $this->assertNotNull($path);
        Storage::disk('public')->assertExists($path);
    }
}
