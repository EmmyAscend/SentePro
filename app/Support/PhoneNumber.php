<?php

namespace App\Support;

class PhoneNumber
{
    /**
     * Normalizes a Ugandan phone number to the exact format Yo Payments'
     * `Account` field requires: international code prepended, no leading
     * "+" or other punctuation — e.g. 256767117958. Accepts whatever a
     * customer actually types: local (0767117958), plus-prefixed
     * (+256767117958), already-international (256767117958), or a bare
     * 9-digit subscriber number with no prefix at all (767117958).
     *
     * Sending an unnormalized number (customer_phone was previously passed
     * straight through, unmodified) is a real, separate bug from any XML
     * malformation on the gateway side — Yo Payments auto-detects the
     * network (MTN/Airtel) from this exact prefix, so a malformed Account
     * value can mean the request never reaches a real subscriber at all,
     * which looks identical from the outside to "no prompt ever arrived."
     */
    public static function normalizeUganda(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone) ?? '';

        if (str_starts_with($digits, '0')) {
            return '256'.substr($digits, 1);
        }

        if (str_starts_with($digits, '256')) {
            return $digits;
        }

        if (strlen($digits) === 9) {
            return '256'.$digits;
        }

        return $digits;
    }
}
