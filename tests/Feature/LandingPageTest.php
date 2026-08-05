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

    public function test_hero_how_it_works_and_requirements_images_are_reduced_on_desktop(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        // Hero + how-it-works + 3 requirement sections = 5 image boxes
        // shrunk to 3/4 size.
        $this->assertSame(5, substr_count($response->getContent(), 'lg:h-3/4 lg:w-3/4'));
    }

    public function test_requirements_images_are_not_cropped_despite_being_shrunk(): void
    {
        $content = LandingPageContent::current();
        $requirements = $content->requirements;
        $requirements[0]['image_path'] = 'landing-page/fake-individual.jpg';
        $content->update(['requirements' => $requirements]);

        $response = $this->get('/');

        $response->assertOk();
        $html = $response->getContent();

        // Shrunk to 3/4 size, no border/ring frame around the image.
        $this->assertSame(3, substr_count($html, 'overflow-hidden rounded-3xl lg:h-3/4 lg:w-3/4'));
        // Still object-contain (not object-cover), so the smaller box
        // still shows the whole image rather than cropping into it.
        $response->assertSee('aspect-[4/3] w-full object-contain lg:aspect-auto lg:h-full lg:w-full', false);
    }

    public function test_requirements_sections_share_one_background_like_payment_links_does(): void
    {
        $response = $this->get('/');

        $response->assertOk();

        // Both the image column and the text column carry the exact same
        // unprefixed (not lg:-gated) bg-slate-900 the payment-links spotlight
        // uses on both of its own columns, so image and text read as one
        // continuous panel instead of two separate cards.
        $this->assertSame(3, substr_count($response->getContent(), 'bg-slate-900 px-4 py-10 sm:px-6 lg:flex lg:h-full lg:items-center lg:justify-center lg:px-0 lg:py-0'));
        // No more mt-6/lg:mt-0 margin — that was the visible seam between
        // the image and text blocks on mobile the user flagged. Now it's
        // plain vertical padding (matching the image column) instead.
        $this->assertSame(3, substr_count($response->getContent(), 'bg-slate-900 px-4 py-8 sm:px-6 lg:flex lg:flex-col lg:justify-center lg:px-16 lg:py-0'));
    }

    public function test_section_text_sizes_default_to_a_larger_desktop_size(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('--sh-desktop: 48px', false);
        // Corrected to 24px (a 50% heading:description ratio) after the
        // earlier literal-double (40px) read as barely distinguishable
        // from the 48px heading and wrapped excessively.
        $response->assertSee('--sd-desktop: 24px', false);
    }

    public function test_sections_are_separated_by_one_centimeter_of_space(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $content = $response->getContent();

        // One boundary per top-level section (payment logos, requirements,
        // features/balances group, payment links, how it works,
        // gateways/faq group, cta banner).
        $this->assertSame(7, substr_count($content, 'mt-[1cm]'));
        // Between each alternating requirement section, features→balances,
        // and gateways→faq, inside their shared wrappers.
        $this->assertSame(3, substr_count($content, 'space-y-[1cm]'));
    }

    public function test_requirements_payment_links_and_how_it_works_buttons_do_not_stretch_full_width_on_desktop(): void
    {
        $response = $this->get('/');

        $response->assertOk();

        // Each button sits inside a `lg:flex lg:flex-col` column, which
        // stretches children to full width by default; lg:self-start opts
        // the button back out to its natural (short) width. 3 requirement
        // register buttons + payment-links "Get started" + how-it-works CTA.
        $this->assertSame(5, substr_count($response->getContent(), 'lg:self-start'));
    }

    public function test_requirements_sections_are_more_compact(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('lg:min-h-[15rem]', false);
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

    public function test_footer_logo_is_double_the_size_of_the_nav_logo(): void
    {
        $response = $this->get('/');

        $response->assertOk();

        // text-5xl (3rem) is exactly double text-2xl (1.5rem). Only the
        // footer's logo grew — the nav's own logo is untouched.
        $response->assertSee('font-pacifico text-lime-400 text-2xl"', false);
        $response->assertSee('font-pacifico text-lime-400 text-5xl"', false);
    }

    public function test_requirements_images_have_no_border_or_ring(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertDontSee('ring-1 ring-white/10 lg:h-3/4 lg:w-3/4', false);
        // Still present without the ring (3 requirement image frames).
        $this->assertSame(3, substr_count($response->getContent(), 'overflow-hidden rounded-3xl lg:h-3/4 lg:w-3/4'));
    }

    public function test_footer_menu_items_are_a_quarter_larger(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $html = $response->getContent();

        // text-xs (12px) * 1.25 = 15px exactly. 3 link columns by default
        // (Product/Company/Legal; Contact only renders when configured).
        $this->assertSame(3, substr_count($html, 'mt-4 space-y-2 text-[15px] text-slate-300'));
        $this->assertStringNotContainsString('mt-4 space-y-2 text-xs text-slate-300', $html);
    }

    public function test_public_site_uses_syne_as_its_font_without_touching_the_logo(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $html = $response->getContent();

        // Syne is now the single shared font for both the public site and
        // the dashboard, applied via the same font-sans class both layouts
        // already used (no more separate font-theme/Dai Banna SIL split).
        $this->assertStringContainsString('syne', $html);
        $this->assertStringContainsString('font-sans bg-slate-950 text-white', $html);
        // The brand-mark's own font-pacifico class is unaffected.
        $response->assertSee('font-pacifico text-lime-400 text-2xl"', false);
        $response->assertSee('font-pacifico text-lime-400 text-5xl"', false);
    }

    public function test_requirements_title_is_doubled_on_mobile(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        // ~16px inherited base doubled to an explicit 32px, one per item.
        $this->assertSame(3, substr_count($response->getContent(), 'text-[32px] font-semibold leading-tight text-white'));
    }

    public function test_desktop_description_text_uses_a_modest_consistent_scale(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $html = $response->getContent();

        // The first attempt at "double the descriptions" (arbitrary
        // lg:text-[32px]/[28px] values, and the shared admin field going to
        // 40px against a 48px heading) produced barely-there heading
        // contrast, excessive wrapping, and — for Feature cards and
        // how-it-works steps, whose titles had no desktop override at all —
        // descriptions literally rendering bigger than their own titles.
        // Replaced with Tailwind's semantic scale (which pairs a sensible
        // line-height automatically, unlike bare arbitrary values) at more
        // modest, consistent sizes: lg:text-xl (20px) for plain-paragraph
        // subtext under a section heading, lg:text-base/lg:text-lg for
        // smaller supporting text.
        // 6 section subtext paragraphs (requirements/features/balances/
        // gateways/faq/cta) + 8 feature-card and how-it-works titles (see
        // the hierarchy test below) + 5 footer elements (tagline, 3 default
        // menu columns, copyright row — Contact only renders when
        // configured) also use lg:text-xl = 19 total.
        $this->assertSame(19, substr_count($html, 'lg:text-xl'));
        $this->assertStringNotContainsString('lg:text-[32px]', $html);
        $this->assertStringNotContainsString('lg:text-[28px]', $html);
    }

    public function test_feature_card_and_how_it_works_titles_stay_bigger_than_their_own_description(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $html = $response->getContent();

        // Both titles previously had no desktop size override at all, while
        // their sibling description got bumped — an inverted hierarchy the
        // user flagged directly ("headings smaller than descriptions").
        // 4 feature cards + 4 how-it-works steps = 8 of each.
        $this->assertSame(8, substr_count($html, 'font-semibold text-white lg:text-xl'));
        $this->assertSame(8, substr_count($html, 'text-sm text-slate-400 lg:text-base'));
    }

    public function test_footer_elements_match_the_description_text_size_on_desktop(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $html = $response->getContent();

        // Tagline, the 3 default menu columns, and the copyright row all
        // grow to lg:text-xl on desktop — matching the same size the CTA
        // banner's own subtext already uses, per the "make them like the
        // ticked paragraph" request. Mobile keeps its existing smaller size.
        $response->assertSee('mt-3 max-w-xs text-xs text-slate-400 lg:text-xl', false);
        $this->assertSame(3, substr_count($html, 'mt-4 space-y-2 text-[15px] text-slate-300 lg:text-xl'));
        $response->assertSee('text-xs text-slate-500 sm:flex-row sm:items-center sm:gap-1 sm:px-6 lg:px-8 lg:text-xl', false);
    }

    public function test_headings_use_tight_line_height(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $html = $response->getContent();

        // Headings driven by an arbitrary CSS-var font-size (--sh-mobile/
        // --sh-desktop, or the headingSize() clamp() inline style) carry no
        // paired line-height the way Tailwind's semantic text-* classes do,
        // which left multi-line headings (e.g. the wrapped Hero headline)
        // with visibly excessive gaps between lines. leading-tight fixes it
        // on every such heading: Hero h1; the Requirements, Features,
        // Balances, Gateways, FAQ, and CTA h2s; Payment-links and
        // how-it-works h2; and Requirements' per-item h3 (x3 by default) —
        // 9 single-render headings + 3 looped h3s = 12 total.
        $this->assertSame(12, substr_count($html, 'leading-tight'));
    }

    public function test_desktop_buttons_are_a_quarter_larger(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $html = $response->getContent();

        // 16px * 1.25 = 20px: hero primary + secondary, payment-links "Get
        // started", how-it-works CTA, cta banner "Register now" = 5.
        $this->assertSame(5, substr_count($html, 'lg:text-[20px]'));
        // 14px * 1.25 = 17.5px: the 3 per-requirement register buttons.
        $this->assertSame(3, substr_count($html, 'lg:text-[17.5px]'));
    }

    public function test_desktop_nav_menu_items_are_a_quarter_larger(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        // 12px * 1.25 = 15px, on the desktop nav's own <nav> wrapper.
        $response->assertSee('text-xs uppercase tracking-wide text-slate-300 md:flex lg:text-[15px]', false);
    }
}
