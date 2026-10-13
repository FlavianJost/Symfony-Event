<?php

declare(strict_types=1);

namespace App\Tests\Unit\Enum;

use App\Enum\RoleUser;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class RoleUserTest extends TestCase
{
    public function testCaseValues(): void
    {
        self::assertSame('ROLE_USER', RoleUser::USER->value);
        self::assertSame('ROLE_ORGANISER', RoleUser::ORGANISER->value);
        self::assertSame('ROLE_ADMIN', RoleUser::ADMIN->value);
    }

    #[DataProvider('caseProvider')]
    public function testFromValue(string $value, RoleUser $expected): void
    {
        self::assertSame($expected, RoleUser::from($value));
    }

    public function testAllCases(): void
    {
        self::assertSame([RoleUser::USER, RoleUser::ORGANISER, RoleUser::ADMIN], RoleUser::cases());
    }

    public static function caseProvider(): iterable
    {
        yield 'user' => ['ROLE_USER', RoleUser::USER];
        yield 'organiser' => ['ROLE_ORGANISER', RoleUser::ORGANISER];
        yield 'admin' => ['ROLE_ADMIN', RoleUser::ADMIN];
    }
}