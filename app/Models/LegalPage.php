<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LegalPage extends Model
{
    public const SLUGS = ['privacy-policy', 'terms-and-conditions', 'refund-policy'];

    protected $fillable = [
        'slug',
        'title',
        'body',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Each legal page is a single platform-wide row per slug, created with
     * starter copy the first time it's needed — same lazy-default pattern as
     * BusinessCalendar::current() and LandingPageContent::current(). The
     * starter copy is a reasonable v1 draft, not reviewed legal advice; a
     * super admin is expected to edit it via /admin/legal-pages before
     * relying on it for real compliance purposes.
     */
    public static function bySlug(string $slug): self
    {
        return static::query()->firstOrCreate(['slug' => $slug], [
            'title' => self::defaultTitle($slug),
            'body' => self::defaultBody($slug),
        ]);
    }

    private static function defaultTitle(string $slug): string
    {
        return match ($slug) {
            'privacy-policy' => 'Privacy Policy',
            'terms-and-conditions' => 'Terms and Conditions',
            'refund-policy' => 'Refund Policy',
        };
    }

    private static function defaultBody(string $slug): string
    {
        return match ($slug) {
            'privacy-policy' => <<<'TEXT'
            This Privacy Policy explains what information SentePro collects, how it is used, and how it is protected.

            Information we collect
            When a business registers, we collect the business's details and the owner's name, email, and password. When a customer pays a business through SentePro, we collect the payment details needed to process the transaction — typically name, email, phone number, and the amount paid — plus any custom fields the business has configured on that payment link.

            How we use information
            We use this information to verify businesses before they can accept live payments, to process and reconcile payments, to generate receipts, to calculate settlements and fees, and to provide support. Every business's data is isolated from every other business at the database level.

            Third parties
            Card payments are processed by Pesapal. Mobile money payments (MTN and Airtel) are processed by Yo Payments. Payment confirmation SMS messages are sent via EgoSMS. We share only the information each of these providers needs to complete a transaction.

            Data retention and security
            Sensitive actions taken on the platform are recorded in an audit log. We retain transaction and account records for as long as needed to provide the service and meet our legal and accounting obligations.

            Your rights
            You may request access to, correction of, or deletion of your personal information, subject to our obligation to retain financial records.

            Contact
            Questions about this policy can be directed to the business you transacted with, or to SentePro support.

            [This is starter policy text — a super admin should review and finalize it before relying on it for real compliance purposes.]
            TEXT,
            'terms-and-conditions' => <<<'TEXT'
            These Terms and Conditions govern the use of SentePro by businesses and their customers.

            The service
            SentePro is a payment collection platform that lets registered, verified businesses accept card and mobile money payments and manage settlements from a single dashboard, without owning a payment gateway directly.

            Business accounts
            A business must submit accurate registration details and complete verification before it can accept live payments. A business is responsible for the accuracy of the payment links, QR codes, and custom fields it configures, and for the conduct of any staff it invites to its account.

            Fees
            Gateway and platform fees are calculated per transaction and per settlement, based on the fee structures and settlement methods configured for the business's provider. Current fees are shown before a settlement is requested.

            Prohibited use
            The platform may not be used to collect payment for illegal goods or services, or in any way that violates applicable law.

            Liability
            SentePro is provided on an "as is" basis. To the fullest extent permitted by law, SentePro is not liable for indirect or consequential losses arising from use of the platform.

            Changes
            These terms may be updated from time to time; continued use of the platform after a change constitutes acceptance of the updated terms.

            [This is starter terms text — a super admin should review and finalize it, including governing law and dispute resolution, before relying on it for real compliance purposes.]
            TEXT,
            'refund-policy' => <<<'TEXT'
            This Refund Policy explains how refunds work on SentePro.

            Card payments (Pesapal)
            A business can refund a completed card transaction in full or in part, directly from its transaction ledger. When a refund is processed, the platform and gateway fee originally charged on that transaction is reversed proportionally to the amount refunded. Refunds are sent back to the original card used, and may take several business days to appear depending on the customer's card issuer.

            Mobile money payments (Yo Payments — MTN and Airtel)
            Mobile money transactions cannot currently be reversed through the SentePro platform. A business that needs to return funds paid by mobile money must arrange this directly with the customer.

            Requesting a refund
            Refunds are initiated by the business that received the payment, from its own dashboard. SentePro does not process refund requests on a business's behalf.

            Disputes
            If a customer believes a charge was made in error, they should first contact the business they paid. If the matter cannot be resolved directly, either party may raise it through SentePro's dispute process.

            [This is starter policy text — a super admin should review and finalize it before relying on it for real compliance purposes.]
            TEXT,
        };
    }
}
