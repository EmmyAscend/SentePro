<?php

namespace App\Models;

use App\Enums\BusinessType;
use Illuminate\Database\Eloquent\Model;

class LandingPageContent extends Model
{
    /**
     * Registration types a Requirements section can link its register button
     * to, so `/business/register?type=...` lands a visitor straight on the
     * matching field group instead of asking again. '' means "no particular
     * type" — the register button just links to the plain registration page.
     */
    public const REQUIREMENT_TYPE_OPTIONS = [
        '' => 'None',
        BusinessType::Individual->value => 'Individual',
        BusinessType::Business->value => 'Business',
        BusinessType::Ngo->value => 'NGO',
    ];

    /**
     * Shared "T-shirt size" vocabulary every individually-configurable
     * heading picks from, rendered as fluid clamp() CSS so a heading still
     * scales smoothly between the given floor and ceiling on every screen
     * width instead of jumping at fixed breakpoints.
     */
    public const HEADING_SIZES = [
        'xs' => 'clamp(1rem,2.5vw,1.25rem)',
        'sm' => 'clamp(1.125rem,3vw,1.5rem)',
        'md' => 'clamp(1.25rem,3.5vw,1.875rem)',
        'lg' => 'clamp(1.5rem,4.5vw,2.25rem)',
        'xl' => 'clamp(1.75rem,5vw,3rem)',
        '2xl' => 'clamp(2rem,6vw,3.75rem)',
    ];

    /**
     * Every individually-sizeable heading on the public site, keyed by the
     * identifier a super admin picks a size for, with its default tier and
     * an admin-facing label.
     */
    public const HEADING_KEYS = [
        'hero' => ['label' => 'Hero headline', 'default' => 'md'],
        'for_business' => ['label' => '"Run your payment operations" card', 'default' => 'sm'],
        'for_customers' => ['label' => '"A fast, familiar checkout" card', 'default' => 'sm'],
        'requirements' => ['label' => '"Who can use SentePro?"', 'default' => 'md'],
        'features' => ['label' => '"Why SentePro?"', 'default' => 'md'],
        'balances' => ['label' => '"One dashboard for every balance"', 'default' => 'md'],
        'payment_links' => ['label' => '"Share a link or QR code..."', 'default' => 'md'],
        'how_it_works' => ['label' => '"It\'s simple to start using SentePro"', 'default' => 'md'],
        'gateways' => ['label' => '"Supported payment ecosystem"', 'default' => 'md'],
        'faq' => ['label' => '"Common questions"', 'default' => 'md'],
        'cta' => ['label' => 'CTA banner heading', 'default' => 'lg'],
    ];

    protected $fillable = [
        'hero_badge_text',
        'hero_headline',
        'hero_subtext',
        'hero_cta_text',
        'features',
        'features_heading',
        'features_subtext',
        'faqs',
        'faq_heading',
        'faq_subtext',
        'requirements',
        'requirements_heading',
        'requirements_subtext',
        'cta_banner_heading',
        'cta_banner_subtext',
        'gateways_heading',
        'gateways_subtext',
        'balances_heading',
        'balances_subtext',
        'payment_links_heading',
        'payment_links_subtext',
        'register_prompt_heading',
        'register_individual_title',
        'register_individual_description',
        'register_business_title',
        'register_business_description',
        'register_ngo_title',
        'register_ngo_description',
        'contact_location',
        'contact_phone',
        'footer_tagline',
        'heading_sizes',
        'section_heading_size_px',
        'section_description_size_px',
        'hero_image_path',
        'how_it_works_heading',
        'how_it_works_steps',
        'how_it_works_cta_text',
        'how_it_works_image_path',
        'payment_links_image_path',
        'payment_logos',
    ];

    protected $casts = [
        'features' => 'array',
        'faqs' => 'array',
        'requirements' => 'array',
        'payment_logos' => 'array',
        'heading_sizes' => 'array',
        'how_it_works_steps' => 'array',
    ];

    /**
     * The fluid clamp() CSS to use for a given heading, honoring a super
     * admin's chosen tier for that heading if one was ever saved, falling
     * back to that heading's own default tier otherwise.
     */
    public function headingSize(string $key): string
    {
        $tier = $this->heading_sizes[$key]
            ?? self::HEADING_KEYS[$key]['default']
            ?? 'md';

        return self::HEADING_SIZES[$tier] ?? self::HEADING_SIZES['md'];
    }

    /**
     * The landing page's content is a single platform-wide row, created with
     * today's copy as defaults the first time it's needed.
     */
    public static function current(): self
    {
        return static::query()->firstOrCreate([], [
            'hero_badge_text' => 'East Africa payment infrastructure',
            'hero_headline' => 'Collect Payments. Settle Faster. Grow Your Business.',
            'hero_subtext' => 'Launch modern payment collection for your business without owning a gateway. SentePro gives you a secure collection layer, verified onboarding, and settlement-ready workflows.',
            'hero_cta_text' => 'Start free onboarding',
            'requirements_heading' => 'Who can use SentePro?',
            'requirements_subtext' => "Whatever kind of organization you run, here's what you'll need to get verified.",
            'requirements' => [
                ['title' => 'Individuals', 'description' => 'Freelancers and sole proprietors can collect payments too — a valid form of identification and your contact details are all you need to get started.', 'type' => 'individual', 'image_path' => null],
                ['title' => 'Businesses', 'description' => "Any registered business can start collecting payments — you'll need your business registration number, trading name, and expected monthly transaction volume.", 'type' => 'business', 'image_path' => null],
                ['title' => 'NGOs', 'description' => "Registered non-profits and NGOs can collect donations and program payments — you'll need your registration certificate details and organization contact information to get verified.", 'type' => 'ngo', 'image_path' => null],
            ],
            'features_heading' => 'Why SentePro?',
            'features_subtext' => 'Fast, flexible, and secure payment collection for growing businesses.',
            'features' => [
                ['title' => 'Unified payment collection', 'description' => 'Collect through one marketplace-ready flow without requiring each business to maintain its own gateway.'],
                ['title' => 'Verified business onboarding', 'description' => 'Capture business, owner, and documentation details under a production-safe verification pipeline.'],
                ['title' => 'Role-aware access', 'description' => 'Super admins, business admins, and staff all operate through structured, permission-based workflows.'],
                ['title' => 'Transparent settlement fees', 'description' => 'Every settlement method shows its fees and timing upfront, locked in the moment you request a payout.'],
            ],
            'faqs' => [
                ['question' => 'How do I register my business on SentePro?', 'answer' => "Submit your business details and an owner account together — you're logged in immediately, and a super admin reviews and approves your business before you can accept live payments."],
                ['question' => 'Is my money safe and secure?', 'answer' => "Every business's wallet, transactions, and settlements are isolated from every other business at the database level, and every sensitive action is recorded in an audit log."],
                ['question' => 'Which payment gateways does SentePro support?', 'answer' => 'Pesapal for card payments, and Yo Payments for MTN and Airtel mobile money.'],
                ['question' => 'How long do settlements take?', 'answer' => 'It depends on the settlement method you choose — each one shows its own processing time and fees before you request a payout.'],
                ['question' => 'Can I refund a customer?', 'answer' => 'Yes — full or partial refunds are supported for card transactions, with the fee automatically reversed proportionally.'],
            ],
            'faq_heading' => 'Frequently Asked Questions',
            'faq_subtext' => 'Find answers to frequently asked questions about SentePro.',
            'how_it_works_heading' => "It's simple to start using SentePro",
            'how_it_works_steps' => [
                ['title' => 'Register your business', 'description' => 'Submit your business and owner details in one form.'],
                ['title' => 'Get verified', 'description' => 'A super admin reviews and approves your business.'],
                ['title' => 'Connect a gateway', 'description' => 'Enable Pesapal for cards, Yo Payments for mobile money.'],
                ['title' => 'Collect & settle', 'description' => 'Share payment links and request settlements to your bank or wallet.'],
            ],
            'how_it_works_cta_text' => 'Get started now',
            'cta_banner_heading' => 'Get started for $0. No setup fees.',
            'cta_banner_subtext' => 'Register your business today and start collecting payments as soon as you\'re verified.',
            'gateways_heading' => 'Supported payment ecosystem',
            'gateways_subtext' => 'Pesapal for cards, Yo Payments for mobile money.',
            'balances_heading' => 'One dashboard for every balance',
            'balances_subtext' => 'See exactly where your money is — available to withdraw, reserved for settlement, or already paid out.',
            'payment_links_heading' => 'Share a link or QR code, get paid instantly',
            'payment_links_subtext' => 'Every payment link comes with a scannable QR code and a copyable checkout URL — no integration work required to start collecting.',
            'register_prompt_heading' => 'What are you registering? Choose one below',
            'register_individual_title' => 'Individual',
            'register_individual_description' => 'Freelancers and sole proprietors collecting payments.',
            'register_business_title' => 'Business',
            'register_business_description' => 'Registered companies collecting payments for goods or services.',
            'register_ngo_title' => 'Non-Profit Organisation',
            'register_ngo_description' => 'NGOs collecting donations and program payments.',
            'footer_tagline' => 'Payment collection infrastructure for East African businesses.',
            'heading_sizes' => array_map(fn (array $heading) => $heading['default'], self::HEADING_KEYS),
            'section_heading_size_px' => 48,
            'section_description_size_px' => 24,
            'payment_logos' => [
                ['label' => 'Visa', 'image_path' => null],
                ['label' => 'Mastercard', 'image_path' => null],
                ['label' => 'MTN', 'image_path' => null],
                ['label' => 'Airtel', 'image_path' => null],
            ],
        ]);
    }
}
