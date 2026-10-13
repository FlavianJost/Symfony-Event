<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Event;
use App\Entity\Registration;
use App\Entity\User;
use App\Enum\RegistrationStatus;
use App\Tests\Unit\ProvidesValidator;
use PHPUnit\Framework\TestCase;

final class RegistrationTest extends TestCase
{
    use ProvidesValidator;

    public function testDefaultStatusIsConfirmedAndCreatedAtIsSet(): void
    {
        $registration = new Registration();

        self::assertNull($registration->getId());
        self::assertNull($registration->getEvent());
        self::assertNull($registration->getUser());
        self::assertSame(RegistrationStatus::Confirmed, $registration->getStatus());
        self::assertNotNull($registration->getCreatedAt());
    }

    public function testGettersAndSetters(): void
    {
        $registration = new Registration();
        $event = new Event();
        $event->setTitle('Meetup PHP');
        $user = new User();
        $user->setUsername('john');
        $createdAt = new \DateTimeImmutable('2026-03-01 09:00:00');

        $registration->setEvent($event)
            ->setUser($user)
            ->setStatus(RegistrationStatus::Cancelled)
            ->setCreatedAt($createdAt);

        self::assertSame($event, $registration->getEvent());
        self::assertSame($user, $registration->getUser());
        self::assertSame(RegistrationStatus::Cancelled, $registration->getStatus());
        self::assertSame($createdAt, $registration->getCreatedAt());
    }

    public function testStringConversionReturnsUserAndEvent(): void
    {
        $registration = new Registration();
        $event = new Event();
        $event->setTitle('Meetup PHP');
        $user = new User();
        $user->setUsername('john');

        $registration->setEvent($event)->setUser($user);

        self::assertSame('john - Meetup PHP', (string) $registration);
    }

    public function testValidationRequiresEventAndUser(): void
    {
        $violations = $this->validator()->validate(new Registration());

        self::assertSame(['event', 'user'], $this->propertyPaths($violations));
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
