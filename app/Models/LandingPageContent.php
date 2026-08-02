<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LandingPageContent extends Model
{
    protected $fillable = [
        'hero_badge_text',
        'hero_headline',
        'hero_subtext',
        'features',
        'faqs',
        'requirements',
        'cta_banner_heading',
        'cta_banner_subtext',
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
    ];

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
                ['title' => 'NGOs', 'description' => "Registered non-profits and NGOs can collect donations and program payments — you'll need your registration certificate details and organization contact information to get verified."],
                ['title' => 'Businesses', 'description' => "Any registered business can start collecting payments — you'll need your business registration number, trading name, and expected monthly transaction volume."],
                ['title' => 'Individuals', 'description' => 'Freelancers and sole proprietors can collect payments too — a valid form of identification and your contact details are all you need to get started.'],
            ],
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
            'cta_banner_heading' => 'Get started for $0. No setup fees.',
            'cta_banner_subtext' => 'Register your business today and start collecting payments as soon as you\'re verified.',
            'payment_logos' => [
                ['label' => 'Visa', 'image_path' => null],
                ['label' => 'Mastercard', 'image_path' => null],
                ['label' => 'MTN', 'image_path' => null],
                ['label' => 'Airtel', 'image_path' => null],
            ],
        ]);
    }
}
