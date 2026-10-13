<?php

declare(strict_types=1);

namespace App\Tests\Unit\Enum;

use App\Enum\EventStatus;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class EventStatusTest extends TestCase
{
    public function testCaseValues(): void
    {
        self::assertSame('draft', EventStatus::Draft->value);
        self::assertSame('published', EventStatus::Published->value);
        self::assertSame('cancelled', EventStatus::Cancelled->value);
    }

    #[DataProvider('caseProvider')]
    public function testFromValue(string $value, EventStatus $expected): void
    {
        self::assertSame($expected, EventStatus::from($value));
    }

    public function testAllCases(): void
    {
        self::assertSame([EventStatus::Draft, EventStatus::Published, EventStatus::Cancelled], EventStatus::cases());
    }

    public static function caseProvider(): iterable
    {
        yield 'draft' => ['draft', EventStatus::Draft];
        yield 'published' => ['published', EventStatus::Published];
        yield 'cancelled' => ['cancelled', EventStatus::Cancelled];
    }
}