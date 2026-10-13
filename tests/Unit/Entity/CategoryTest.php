<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Category;
use App\Entity\Event;
use App\Tests\Unit\ProvidesValidator;
use PHPUnit\Framework\TestCase;

final class CategoryTest extends TestCase
{
    use ProvidesValidator;

    public function testConstructorInitializesEmptyEventCollection(): void
    {
        $category = new Category();

        self::assertNull($category->getId());
        self::assertCount(0, $category->getEvents());
    }

    public function testGettersAndSetters(): void
    {
        $category = new Category();
        $category->setName('Concert');
        $category->setSlug('concert');

        self::assertSame('Concert', $category->getName());
        self::assertSame('concert', $category->getSlug());
    }

    public function testStringConversionReturnsName(): void
    {
        $category = new Category();
        $category->setName('Concert');

        self::assertSame('Concert', (string) $category);
    }

    public function testAddEventSetsBidirectionalLinkOnce(): void
    {
        $category = new Category();
        $event = new Event();

        $category->addEvent($event);
        $category->addEvent($event);

        self::assertCount(1, $category->getEvents());
        self::assertTrue($category->getEvents()->contains($event));
        self::assertSame($category, $event->getCategory());
    }

    public function testRemoveEventRemovesFromCollection(): void
    {
        $category = new Category();
        $event = new Event();
        $category->addEvent($event);

        $category->removeEvent($event);

        self::assertCount(0, $category->getEvents());
    }

    public function testBlankCategoryFailsValidation(): void
    {
        $violations = $this->validator()->validate(new Category());

        self::assertNotCount(0, $violations);
    }
}
