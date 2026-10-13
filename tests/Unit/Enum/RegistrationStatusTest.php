<?php

declare(strict_types=1);

namespace App\Tests\Unit\Enum;

use App\Enum\RegistrationStatus;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class RegistrationStatusTest extends TestCase
{
    public function testCaseValues(): void
    {
        self::assertSame('confirmed', RegistrationStatus::Confirmed->value);
        self::assertSame('waitlist', RegistrationStatus::Waitlist->value);
        self::assertSame('cancelled', RegistrationStatus::Cancelled->value);
    }

    #[DataProvider('caseProvider')]
    public function testFromValue(string $value, RegistrationStatus $expected): void
    {
        self::assertSame($expected, RegistrationStatus::from($value));
    }

    public function testAllCases(): void
    {
        self::assertSame(
            [RegistrationStatus::Confirmed, RegistrationStatus::Waitlist, RegistrationStatus::Cancelled],
            RegistrationStatus::cases()
        );
    }

    public static function caseProvider(): iterable
    {
        yield 'confirmed' => ['confirmed', RegistrationStatus::Confirmed];
        yield 'waitlist' => ['waitlist', RegistrationStatus::Waitlist];
        yield 'cancelled' => ['cancelled', RegistrationStatus::Cancelled];
    }
}