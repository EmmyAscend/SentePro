<?php

namespace Tests\Unit;

use App\Support\PhoneNumber;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PhoneNumberTest extends TestCase
{
    /**
     * @return array<string, array{string, string}>
     */
    public static function ugandaNumberProvider(): array
    {
        return [
            'local format with leading zero' => ['0767117958', '256767117958'],
            'plus-prefixed international format' => ['+256767117958', '256767117958'],
            'already-normalized international format' => ['256767117958', '256767117958'],
            'bare 9-digit subscriber number, no prefix' => ['767117958', '256767117958'],
            'formatted with spaces' => ['0767 117 958', '256767117958'],
            'formatted with dashes' => ['0767-117-958', '256767117958'],
        ];
    }

    #[DataProvider('ugandaNumberProvider')]
    public function test_normalize_uganda_produces_the_exact_format_yo_payments_requires(string $input, string $expected): void
    {
        $this->assertSame($expected, PhoneNumber::normalizeUganda($input));
    }
}
