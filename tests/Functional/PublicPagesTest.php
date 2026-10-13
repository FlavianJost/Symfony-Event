<?php

declare(strict_types=1);

namespace App\Tests\Functional;

final class PublicPagesTest extends BaseWebTestCase
{
    public function testHomepageLoads(): void
    {
        $this->client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Organisez');
        self::assertSelectorTextContains('h2', 'Prochains événements');
    }

    public function testHomepageShowsPublishedEvents(): void
    {
        $event = $this->makeEvent();

        $this->client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h2', 'Prochains événements');
        self::assertSelectorTextContains('body', $event->getTitle());

        $this->cleanup([$event]);
    }

    public function testEventsListLoads(): void
    {
        $this->client->request('GET', '/events');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Tous les événements');
    }

    public function testEventsListShowsPublishedEvent(): void
    {
        $event = $this->makeEvent();

        $this->client->request('GET', '/events');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', $event->getTitle());

        $this->cleanup([$event]);
    }

    public function testEventDetailLoads(): void
    {
        $event = $this->makeEvent();

        $this->client->request('GET', '/events/' . $event->getSlug());

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', $event->getTitle());
        self::assertSelectorTextContains('body', $event->getDescription());
        self::assertSelectorTextContains('body', (string) $event->getCapacity());
        self::assertSelectorTextContains('body', 'Inscrits :');

        $this->cleanup([$event]);
    }

    public function testEventDetailUnknownSlugReturns404(): void
    {
        $this->client->request('GET', '/events/slug-qui-nexiste-pas');

        self::assertResponseStatusCodeSame(404);
    }

    public function testHealthCheckReturnsOk(): void
    {
        $this->client->request('GET', '/health-check');

        self::assertResponseIsSuccessful();
        self::assertSame('OK', $this->client->getResponse()->getContent());
    }
}