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
            'hero_cta_text' => 'Start free onboarding',
            'features_heading' => 'Updated features heading',
            'features_subtext' => 'Updated features subtext',
            'features' => array_fill(0, 4, ['title' => 'Feature title', 'description' => 'Feature description']),
            'requirements_heading' => 'Updated requirements heading',
            'requirements_subtext' => 'Updated requirements subtext',
            'requirements' => array_fill(0, 3, ['title' => 'Requirement title', 'description' => 'Requirement description', 'type' => 'business']),
            'faq_heading' => 'Frequently Asked Questions',
            'faq_subtext' => 'Updated FAQ subtext',
            'faqs' => array_fill(0, 5, ['question' => 'A question?', 'answer' => 'An answer.']),
            'cta_banner_heading' => 'Updated banner heading',
            'cta_banner_subtext' => 'Updated banner subtext',
            'gateways_heading' => 'Updated gateways heading',
            'gateways_subtext' => 'Updated gateways subtext',
            'balances_heading' => 'Updated balances heading',
            'balances_subtext' => 'Updated balances subtext',
            'payment_links_heading' => 'Updated payment links heading',
            'payment_links_subtext' => 'Updated payment links subtext',
            'contact_location' => 'Kampala, Uganda',
            'contact_phone' => '+256700000000',
            'footer_tagline' => 'Updated footer tagline',
            'how_it_works_heading' => 'Updated how it works heading',
            'how_it_works_steps' => array_fill(0, 4, ['title' => 'Step title', 'description' => 'Step description']),
            'how_it_works_cta_text' => 'Get started now',
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
            'features' => array_fill(0, 6, ['title' => 'Extra feature', 'description' => 'Extra description']),
            'requirements' => array_fill(0, 5, ['title' => 'Extra requirement', 'description' => 'Extra description']),
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

    public function test_super_admin_can_update_the_gateways_section_text(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $payload = array_merge($this->validPayload(), [
            'gateways_heading' => 'A brand new gateways heading',
            'gateways_subtext' => 'A brand new gateways subtext',
        ]);

        $this->actingAs($superAdmin)->put('/admin/landing-page', $payload)->assertRedirect();

        $content = LandingPageContent::current();
        $this->assertSame('A brand new gateways heading', $content->gateways_heading);
        $this->assertSame('A brand new gateways subtext', $content->gateways_subtext);

        $home = $this->get('/');
        $home->assertSee('A brand new gateways heading');
        $home->assertSee('A brand new gateways subtext');
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

    public function test_super_admin_can_update_the_hero_cta_text(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $payload = array_merge($this->validPayload(), ['hero_cta_text' => 'Join SentePro today']);

        $this->actingAs($superAdmin)->put('/admin/landing-page', $payload)->assertRedirect();

        $this->assertSame('Join SentePro today', LandingPageContent::current()->hero_cta_text);
        $this->get('/')->assertSee('Join SentePro today');
    }

    public function test_super_admin_can_update_the_requirements_heading_and_a_requirements_type(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $payload = array_merge($this->validPayload(), [
            'requirements_heading' => 'A brand new requirements heading',
            'requirements' => [['title' => 'Freelancers', 'description' => 'Solo operators.', 'type' => 'individual']],
        ]);

        $this->actingAs($superAdmin)->put('/admin/landing-page', $payload)->assertRedirect();

        $content = LandingPageContent::current();
        $this->assertSame('A brand new requirements heading', $content->requirements_heading);
        $this->assertSame('individual', $content->requirements[0]['type']);

        $home = $this->get('/');
        $home->assertSee('A brand new requirements heading');
        $home->assertSee(route('business.register', ['type' => 'individual']), false);
    }

    public function test_super_admin_can_upload_a_requirement_image(): void
    {
        Storage::fake('public');
        $superAdmin = User::factory()->superAdmin()->create();

        $requirements = [['title' => 'Freelancers', 'description' => 'Solo operators.', 'type' => 'individual', 'image' => UploadedFile::fake()->image('individual.png')]];

        $payload = array_merge($this->validPayload(), ['requirements' => $requirements]);

        $this->actingAs($superAdmin)->put('/admin/landing-page', $payload)->assertRedirect();

        $path = LandingPageContent::current()->requirements[0]['image_path'];
        $this->assertNotNull($path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_super_admin_can_update_how_it_works_text_and_steps(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $payload = array_merge($this->validPayload(), [
            'how_it_works_heading' => 'A brand new how-it-works heading',
            'how_it_works_steps' => [['title' => 'Sign up', 'description' => 'Create your account.']],
            'how_it_works_cta_text' => 'Begin now',
        ]);

        $this->actingAs($superAdmin)->put('/admin/landing-page', $payload)->assertRedirect();

        $content = LandingPageContent::current();
        $this->assertSame('A brand new how-it-works heading', $content->how_it_works_heading);
        $this->assertCount(1, $content->how_it_works_steps);
        $this->assertSame('Begin now', $content->how_it_works_cta_text);

        $home = $this->get('/');
        $home->assertSee('A brand new how-it-works heading');
        $home->assertSee('Sign up');
        $home->assertSee('Begin now');
    }

    public function test_super_admin_can_update_the_faq_heading(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $payload = array_merge($this->validPayload(), [
            'faq_heading' => 'FAQ - a brand new heading',
            'faq_subtext' => 'A brand new FAQ subtext',
        ]);

        $this->actingAs($superAdmin)->put('/admin/landing-page', $payload)->assertRedirect();

        $content = LandingPageContent::current();
        $this->assertSame('FAQ - a brand new heading', $content->faq_heading);
        $this->assertSame('A brand new FAQ subtext', $content->faq_subtext);

        $home = $this->get('/');
        $home->assertSee('FAQ - a brand new heading');
    }

    public function test_super_admin_can_update_the_features_heading(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $payload = array_merge($this->validPayload(), [
            'features_heading' => 'A brand new features heading',
            'features_subtext' => 'A brand new features subtext',
        ]);

        $this->actingAs($superAdmin)->put('/admin/landing-page', $payload)->assertRedirect();

        $content = LandingPageContent::current();
        $this->assertSame('A brand new features heading', $content->features_heading);
        $this->assertSame('A brand new features subtext', $content->features_subtext);

        $home = $this->get('/');
        $home->assertSee('A brand new features heading');
    }

    public function test_super_admin_can_update_the_balances_heading(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $payload = array_merge($this->validPayload(), [
            'balances_heading' => 'A brand new balances heading',
            'balances_subtext' => 'A brand new balances subtext',
        ]);

        $this->actingAs($superAdmin)->put('/admin/landing-page', $payload)->assertRedirect();

        $content = LandingPageContent::current();
        $this->assertSame('A brand new balances heading', $content->balances_heading);
        $this->assertSame('A brand new balances subtext', $content->balances_subtext);

        $home = $this->get('/');
        $home->assertSee('A brand new balances heading');
    }

    public function test_super_admin_can_update_the_payment_links_heading(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $payload = array_merge($this->validPayload(), [
            'payment_links_heading' => 'A brand new payment links heading',
            'payment_links_subtext' => 'A brand new payment links subtext',
        ]);

        $this->actingAs($superAdmin)->put('/admin/landing-page', $payload)->assertRedirect();

        $content = LandingPageContent::current();
        $this->assertSame('A brand new payment links heading', $content->payment_links_heading);
        $this->assertSame('A brand new payment links subtext', $content->payment_links_subtext);

        $home = $this->get('/');
        $home->assertSee('A brand new payment links heading');
    }

    public function test_icon_fields_are_no_longer_accepted_or_required(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $this->actingAs($superAdmin)->put('/admin/landing-page', $this->validPayload())
            ->assertRedirect()
            ->assertSessionHasNoErrors();
    }
}
