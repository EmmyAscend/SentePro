<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LandingPageContent extends Model
{
    /**
     * Content icons offered to a super admin for Requirements/Features items,
     * matching a curated subset of <x-sidebar-icon>'s registry — excludes
     * pure UI-chrome names (menu/x/chevron) that aren't meaningful as a
     * standalone content icon.
     */
    public const ICON_OPTIONS = [
        'home', 'banknotes', 'link', 'receipt', 'transfer', 'chat', 'flag',
        'book', 'megaphone', 'server', 'webhook', 'chart', 'key', 'users',
        'shield', 'wallet', 'clipboard', 'check',
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
        'hero' => ['label' => 'Hero headline', 'default' => 'xl'],
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
        'features',
        'faqs',
        'requirements',
        'cta_banner_heading',
        'cta_banner_subtext',
        'contact_location',
        'contact_phone',
        'footer_tagline',
        'heading_sizes',
        'hero_image_path',
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
            'requirements' => [
                ['title' => 'NGOs', 'description' => "Registered non-profits and NGOs can collect donations and program payments — you'll need your registration certificate details and organization contact information to get verified.", 'icon' => 'shield'],
                ['title' => 'Businesses', 'description' => "Any registered business can start collecting payments — you'll need your business registration number, trading name, and expected monthly transaction volume.", 'icon' => 'banknotes'],
                ['title' => 'Individuals', 'description' => 'Freelancers and sole proprietors can collect payments too — a valid form of identification and your contact details are all you need to get started.', 'icon' => 'users'],
            ],
            'features' => [
                ['title' => 'Unified payment collection', 'description' => 'Collect through one marketplace-ready flow without requiring each business to maintain its own gateway.', 'icon' => 'link'],
                ['title' => 'Verified business onboarding', 'description' => 'Capture business, owner, and documentation details under a production-safe verification pipeline.', 'icon' => 'check'],
                ['title' => 'Role-aware access', 'description' => 'Super admins, business admins, and staff all operate through structured, permission-based workflows.', 'icon' => 'users'],
                ['title' => 'Transparent settlement fees', 'description' => 'Every settlement method shows its fees and timing upfront, locked in the moment you request a payout.', 'icon' => 'clipboard'],
            ],
            'faqs' => [
                ['question' => 'How do I register my business on SentePro?', 'answer' => "Submit your business details and an owner account together — you're logged in immediately, and a super admin reviews and approves your business before you can accept live payments."],
                ['question' => 'Is my money safe and secure?', 'answer' => "Every business's wallet, transactions, and settlements are isolated from every other business at the database level, and every sensitive action is recorded in an audit log."],
                ['question' => 'Which payment gateways does SentePro support?', 'answer' => 'Pesapal for card payments, and Yo Payments for MTN and Airtel mobile money.'],
                ['question' => 'How long do settlements take?', 'answer' => 'It depends on the settlement method you choose — each one shows its own processing time and fees before you request a payout.'],
                ['question' => 'Can I refund a customer?', 'answer' => 'Yes — full or partial refunds are supported for card transactions, with the fee automatically reversed proportionally.'],
            ],
            'cta_banner_heading' => 'Get started for $0. No setup fees.',
            'cta_banner_subtext' => 'Register your business today and start collecting payments as soon as you\'re verified.',
            'footer_tagline' => 'Payment collection infrastructure for East African businesses.',
            'heading_sizes' => array_map(fn (array $heading) => $heading['default'], self::HEADING_KEYS),
            'payment_logos' => [
                ['label' => 'Visa', 'image_path' => null],
                ['label' => 'Mastercard', 'image_path' => null],
                ['label' => 'MTN', 'image_path' => null],
                ['label' => 'Airtel', 'image_path' => null],
            ],
        ]);
    }
}
