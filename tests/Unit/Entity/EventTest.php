<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Event;
use App\Enum\EventStatus;
use App\Tests\Unit\ProvidesValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolationListInterface;

final class EventTest extends TestCase
{
    use ProvidesValidator;

    public function testDefaultStatusIsDraft(): void
    {
        self::assertSame(EventStatus::Draft, (new Event())->getStatus());
    }

    public function testSettersAreFluent(): void
    {
        $event = new Event();

        self::assertSame($event, $event->setTitle('Titre'));
        self::assertSame($event, $event->setStartAt(new \DateTimeImmutable()));
    }

    public function testGettersAndSetters(): void
    {
        $event = new Event();
        $startAt = new \DateTimeImmutable('2030-05-01 10:00:00');
        $endAt = new \DateTimeImmutable('2030-05-01 18:00:00');

        $event->setTitle('Meetup PHP')
            ->setSlug('meetup-php')
            ->setDescription('Une description.')
            ->setStartAt($startAt)
            ->setEndAt($endAt)
            ->setCapacity(30)
            ->setStatus(EventStatus::Published)
            ->setCoverImage('couverture.jpg');

        self::assertNull($event->getId());
        self::assertSame('Meetup PHP', $event->getTitle());
        self::assertSame('meetup-php', $event->getSlug());
        self::assertSame('Une description.', $event->getDescription());
        self::assertSame($startAt, $event->getStartAt());
        self::assertSame($endAt, $event->getEndAt());
        self::assertSame(30, $event->getCapacity());
        self::assertSame(EventStatus::Published, $event->getStatus());
        self::assertSame('couverture.jpg', $event->getCoverImage());
        self::assertNull($event->getOrganizer());
        self::assertNull($event->getCategory());
    }

    public function testStringConversionReturnsTitle(): void
    {
        $event = new Event();
        $event->setTitle('Meetup PHP');

        self::assertSame('Meetup PHP', (string) $event);
    }

    public function testValidationAcceptChronologicalDates(): void
    {
        $violations = $this->validator()->validate($this->validEvent());

        self::assertCount(0, $violations);
    }

    public function testValidationRejectsInvertedDates(): void
    {
        $event = $this->validEvent();
        $event->setStartAt(new \DateTimeImmutable('2030-05-01 18:00:00'))
            ->setEndAt(new \DateTimeImmutable('2030-05-01 09:00:00'));

        $violations = $this->validator()->validate($event);

        self::assertSame(['endAt'], $this->propertyPaths($violations));
        self::assertSame('La date de fin doit être postérieure à la date de début.', $violations[0]->getMessage());
    }

    public function testValidationRejectsIdenticalDates(): void
    {
        $event = $this->validEvent();
        $sameInstant = new \DateTimeImmutable('2030-05-01 10:00:00');
        $event->setStartAt($sameInstant)
            ->setEndAt($sameInstant);

        $violations = $this->validator()->validate($event);

        self::assertSame(['endAt'], $this->propertyPaths($violations));
    }

    public function testValidationRejectsZeroCapacity(): void
    {
        $event = $this->validEvent();
        $event->setCapacity(0);

        $violations = $this->validator()->validate($event);

        self::assertSame(['capacity'], $this->propertyPaths($violations));
    }

    public function testBlankEventFailsValidation(): void
    {
        $violations = $this->validator()->validate(new Event());

        self::assertNotCount(0, $violations);
    }

    private function validEvent(): Event
    {
        return (new Event())
            ->setTitle('Meetup PHP')
            ->setSlug('meetup-php')
            ->setDescription('Une description.')
            ->setStartAt(new \DateTimeImmutable('2030-05-01 10:00:00'))
            ->setEndAt(new \DateTimeImmutable('2030-05-01 18:00:00'))
            ->setCapacity(30);
    }

    /**
     * @return list<string>
     */
    private function propertyPaths(ConstraintViolationListInterface $violations): array
    {
        $paths = [];
        foreach ($violations as $violation) {
            $paths[] = $violation->getPropertyPath();
        }

        return $paths;
    }
}
