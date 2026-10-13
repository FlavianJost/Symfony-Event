<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\User;
use App\Tests\Unit\ProvidesValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final class UserTest extends TestCase
{
    use ProvidesValidator;

    public function testConstructorSetsCreationAndUpdateTimestamps(): void
    {
        $user = new User();

        self::assertNotNull($user->getCreatedAt());
        self::assertNotNull($user->getUpdatedAt());
    }

    public function testDefaultRolesContainUserRole(): void
    {
        self::assertSame(['ROLE_USER'], (new User())->getRoles());
    }

    public function testSetEmailNormalizesToLowercaseTrimmed(): void
    {
        $user = new User();
        $user->setEmail('  JOHN@Example.COM  ');

        self::assertSame('john@example.com', $user->getEmail());
    }

    public function testGetRolesReturnsUniqueValues(): void
    {
        $user = new User();
        $user->setRoles(['ROLE_USER', 'ROLE_ADMIN', 'ROLE_USER']);

        self::assertSame(['ROLE_USER', 'ROLE_ADMIN'], array_values($user->getRoles()));
    }

    public function testGettersAndSetters(): void
    {
        $user = new User();
        $user->setUsername('john');
        $user->setEmail('john@example.com');
        $user->setPassword('hash');
        $lastLoginAt = new \DateTimeImmutable('2026-01-01 10:00:00');
        $user->setLastLoginAt($lastLoginAt);
        $updatedAt = new \DateTimeImmutable('2026-02-01 10:00:00');
        $user->setUpdatedAt($updatedAt);

        self::assertNull($user->getId());
        self::assertSame('john', $user->getUsername());
        self::assertSame('john@example.com', $user->getEmail());
        self::assertSame('hash', $user->getPassword());
        self::assertSame($lastLoginAt, $user->getLastLoginAt());
        self::assertSame($updatedAt, $user->getUpdatedAt());
    }

    public function testCanSetLastLoginAtToNull(): void
    {
        $user = new User();
        $user->setLastLoginAt(null);

        self::assertNull($user->getLastLoginAt());
    }

    public function testGetUserIdentifierReturnsEmail(): void
    {
        $user = new User();
        $user->setEmail('john@example.com');

        self::assertSame('john@example.com', $user->getUserIdentifier());
    }

    public function testStringConversionReturnsUsername(): void
    {
        $user = new User();
        $user->setUsername('john');

        self::assertSame('john', (string) $user);
    }

    public function testImplementsSecurityInterfaces(): void
    {
        $user = new User();

        self::assertInstanceOf(UserInterface::class, $user);
        self::assertInstanceOf(PasswordAuthenticatedUserInterface::class, $user);
    }

    public function testEraseCredentialsDoesNotClearPassword(): void
    {
        $user = new User();
        $user->setPassword('hash');

        $user->eraseCredentials();

        self::assertSame('hash', $user->getPassword());
    }

    public function testBlankUserFailsValidation(): void
    {
        $violations = $this->validator()->validate(new User());

        self::assertNotCount(0, $violations);
    }

    public function testCompleteUserPassesValidation(): void
    {
        $user = new User();
        $user->setUsername('john');
        $user->setEmail('john@example.com');
        $user->setPassword('hash');
        $user->setLastLoginAt(new \DateTimeImmutable());

        $violations = $this->validator()->validate($user);

        self::assertCount(0, $violations);
    }

    public function testInvalidEmailFailsValidation(): void
    {
        $user = new User();
        $user->setUsername('john');
        $user->setEmail('pas-un-email');
        $user->setPassword('hash');
        $user->setLastLoginAt(new \DateTimeImmutable());

        $violations = $this->validator()->validate($user);

        self::assertSame(['email'], $this->propertyPaths($violations));
    }

    /**
     * @return list<string>
     */
    private function propertyPaths(\Symfony\Component\Validator\ConstraintViolationListInterface $violations): array
    {
        $paths = [];
        foreach ($violations as $violation) {
            $paths[] = $violation->getPropertyPath();
        }

        return $paths;
    }
}
