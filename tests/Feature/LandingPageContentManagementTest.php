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
            'features' => array_fill(0, 4, ['title' => 'Feature title', 'description' => 'Feature description', 'icon' => 'link']),
            'requirements' => array_fill(0, 3, ['title' => 'Requirement title', 'description' => 'Requirement description', 'icon' => 'shield']),
            'faqs' => array_fill(0, 5, ['question' => 'A question?', 'answer' => 'An answer.']),
            'cta_banner_heading' => 'Updated banner heading',
            'cta_banner_subtext' => 'Updated banner subtext',
            'contact_location' => 'Kampala, Uganda',
            'contact_phone' => '+256700000000',
            'footer_tagline' => 'Updated footer tagline',
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
        $home->assertSee('Requirement title');
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

    public function test_super_admin_can_add_more_items_than_the_original_default_count(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $payload = array_merge($this->validPayload(), [
            'features' => array_fill(0, 6, ['title' => 'Extra feature', 'description' => 'Extra description', 'icon' => 'link']),
            'requirements' => array_fill(0, 5, ['title' => 'Extra requirement', 'description' => 'Extra description', 'icon' => 'shield']),
            'faqs' => array_fill(0, 7, ['question' => 'Extra question?', 'answer' => 'Extra answer.']),
            'payment_logos' => array_fill(0, 6, ['label' => 'Extra Network']),
        ]);

        $this->actingAs($superAdmin)->put('/admin/landing-page', $payload)->assertRedirect();

        $content = LandingPageContent::current();
        $this->assertCount(6, $content->features);
        $this->assertCount(5, $content->requirements);
        $this->assertCount(7, $content->faqs);
        $this->assertCount(6, $content->payment_logos);
    }

    public function test_submitting_an_empty_array_is_rejected(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $payload = array_merge($this->validPayload(), ['features' => []]);

        $this->actingAs($superAdmin)->put('/admin/landing-page', $payload)->assertSessionHasErrors('features');
    }

    public function test_super_admin_can_choose_icons_for_requirements_and_features(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $payload = array_merge($this->validPayload(), [
            'requirements' => [['title' => 'Custom requirement', 'description' => 'Custom description', 'icon' => 'wallet']],
            'features' => [['title' => 'Custom feature', 'description' => 'Custom description', 'icon' => 'chart']],
        ]);

        $this->actingAs($superAdmin)->put('/admin/landing-page', $payload)->assertRedirect();

        $content = LandingPageContent::current();
        $this->assertSame('wallet', $content->requirements[0]['icon']);
        $this->assertSame('chart', $content->features[0]['icon']);
    }

    public function test_an_invalid_icon_is_rejected(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $payload = array_merge($this->validPayload(), [
            'requirements' => [['title' => 'Custom requirement', 'description' => 'Custom description', 'icon' => 'not-a-real-icon']],
        ]);

        $this->actingAs($superAdmin)->put('/admin/landing-page', $payload)->assertSessionHasErrors('requirements.0.icon');
    }

    public function test_super_admin_can_update_the_contact_section(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $payload = array_merge($this->validPayload(), [
            'contact_location' => 'Kampala, Uganda',
            'contact_phone' => '+256700000000',
        ]);

        $this->actingAs($superAdmin)->put('/admin/landing-page', $payload)->assertRedirect();

        $content = LandingPageContent::current();
        $this->assertSame('Kampala, Uganda', $content->contact_location);
        $this->assertSame('+256700000000', $content->contact_phone);

        $this->get('/')->assertSee('Kampala, Uganda');
    }

    public function test_super_admin_can_update_the_footer_tagline(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $payload = array_merge($this->validPayload(), ['footer_tagline' => 'A brand new footer tagline']);

        $this->actingAs($superAdmin)->put('/admin/landing-page', $payload)->assertRedirect();

        $this->assertSame('A brand new footer tagline', LandingPageContent::current()->footer_tagline);
        $this->get('/')->assertSee('A brand new footer tagline');
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

    public function test_a_payment_logo_can_be_saved_with_only_an_image_and_no_label(): void
    {
        Storage::fake('public');
        $superAdmin = User::factory()->superAdmin()->create();

        $logos = array_fill(0, 4, ['label' => 'Visa']);
        $logos[] = ['label' => '', 'image' => UploadedFile::fake()->image('new-network.png')];

        $payload = array_merge($this->validPayload(), ['payment_logos' => $logos]);

        $this->actingAs($superAdmin)->put('/admin/landing-page', $payload)->assertRedirect()->assertSessionHasNoErrors();

        $content = LandingPageContent::current();
        $this->assertCount(5, $content->payment_logos);
        $this->assertNotNull($content->payment_logos[4]['image_path']);
    }

    public function test_resubmitting_a_payment_logos_existing_image_path_keeps_the_image(): void
    {
        Storage::fake('public');
        $superAdmin = User::factory()->superAdmin()->create();

        $logos = array_fill(0, 4, ['label' => 'Visa']);
        $logos[0]['image'] = UploadedFile::fake()->image('visa.png');
        $this->actingAs($superAdmin)->put('/admin/landing-page', array_merge($this->validPayload(), ['payment_logos' => $logos]));
        $originalPath = LandingPageContent::current()->payment_logos[0]['image_path'];

        // The real admin form carries each logo's current image_path forward
        // as a hidden field on every save, exactly like this.
        $resubmitted = LandingPageContent::current()->payment_logos;
        $this->actingAs($superAdmin)->put('/admin/landing-page', array_merge($this->validPayload(), ['payment_logos' => $resubmitted]))->assertRedirect();

        $this->assertSame($originalPath, LandingPageContent::current()->payment_logos[0]['image_path']);
        Storage::disk('public')->assertExists($originalPath);
    }

    public function test_removing_a_logo_does_not_corrupt_the_remaining_images(): void
    {
        Storage::fake('public');
        $superAdmin = User::factory()->superAdmin()->create();

        $logos = array_fill(0, 4, ['label' => 'Visa']);
        $logos[0]['image'] = UploadedFile::fake()->image('first.png');
        $logos[1]['image'] = UploadedFile::fake()->image('second.png');
        $this->actingAs($superAdmin)->put('/admin/landing-page', array_merge($this->validPayload(), ['payment_logos' => $logos]));

        $saved = LandingPageContent::current()->payment_logos;
        $secondLogoPath = $saved[1]['image_path'];

        // Remove the first logo — the client re-sends the remaining items
        // (each carrying its own real image_path) shifted down by one index.
        $afterRemoval = array_values(array_slice($saved, 1));
        $this->actingAs($superAdmin)->put('/admin/landing-page', array_merge($this->validPayload(), ['payment_logos' => $afterRemoval]))->assertRedirect();

        $content = LandingPageContent::current();
        $this->assertCount(3, $content->payment_logos);
        $this->assertSame($secondLogoPath, $content->payment_logos[0]['image_path']);
    }

    public function test_super_admin_can_adjust_an_individual_headings_size(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $payload = array_merge($this->validPayload(), ['heading_sizes' => ['hero' => 'sm']]);

        $this->actingAs($superAdmin)->put('/admin/landing-page', $payload)->assertRedirect();

        $this->assertSame('sm', LandingPageContent::current()->heading_sizes['hero']);
        $this->get('/')->assertSee(LandingPageContent::HEADING_SIZES['sm'], false);
    }

    public function test_an_invalid_heading_size_is_rejected(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $payload = array_merge($this->validPayload(), ['heading_sizes' => ['hero' => 'not-a-real-size']]);

        $this->actingAs($superAdmin)->put('/admin/landing-page', $payload)->assertSessionHasErrors('heading_sizes.hero');
    }
}
