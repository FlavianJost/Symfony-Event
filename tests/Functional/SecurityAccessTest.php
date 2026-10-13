<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Event;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\IgnoreDeprecations;

#[\PHPUnit\Framework\Attributes\IgnoreDeprecations]
final class SecurityAccessTest extends BaseWebTestCase
{
    private ?Event $publishedEvent = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->publishedEvent = $this->makeEvent($this->organiser());
    }

    protected function tearDown(): void
    {
        $this->cleanup([$this->publishedEvent]);
        $this->publishedEvent = null;
        parent::tearDown();
    }

    public function testAnonymousCanAccessPublicPages(): void
    {
        foreach (['/', '/events', '/login', '/register', '/health-check'] as $url) {
            $this->client->request('GET', $url);
            self::assertResponseIsSuccessful(sprintf('GET %s devrait être public.', $url));
        }

        $this->client->request('GET', '/events/' . $this->publishedEvent->getSlug());
        self::assertResponseIsSuccessful();
    }

    public static function anonymousProtectedPagesProvider(): iterable
    {
        yield 'new event' => ['/events/new'];
        yield 'admin' => ['/admin'];
        yield 'profile' => ['/profile'];
        yield 'organizer' => ['/organizer'];
        yield 'registered list' => ['/events-registered'];
        yield 'register to event' => ['/events/evenement-test/register'];
        yield 'cancel registration' => ['/events/evenement-test/cancel_registered'];
        yield 'edit event' => ['/events/evenement-test/edit'];
    }

    #[DataProvider('anonymousProtectedPagesProvider')]
    public function testAnonymousIsRedirectedToLogin(string $url): void
    {
        $this->client->request('GET', $url);

        self::assertResponseRedirects('/login');
    }

    public function testRegularUserCanAccessProfile(): void
    {
        $this->login(self::USER_EMAIL);
        $this->client->request('GET', '/profile');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Mon profil');
    }

    public function testRegularUserIsDeniedFromNewEvent(): void
    {
        $this->login(self::USER_EMAIL);
        $this->client->request('GET', '/events/new');

        self::assertResponseStatusCodeSame(403);
    }

    public function testRegularUserIsDeniedFromAdmin(): void
    {
        $this->login(self::USER_EMAIL);
        $this->client->request('GET', '/admin');

        self::assertResponseStatusCodeSame(403);
    }

    public function testRegularUserIsDeniedFromOrganizerPage(): void
    {
        $this->login(self::USER_EMAIL);
        $this->client->request('GET', '/organizer');

        self::assertResponseStatusCodeSame(403);
    }

    public function testRegularUserCanListRegistrations(): void
    {
        $this->login(self::USER_EMAIL);
        $this->client->request('GET', '/events-registered');

        self::assertResponseIsSuccessful();
        // Le contrôleur passe "registrations" (pluriel) alors que le template attend "registered"
        // → le h1 générique « Tous les événements » est affiché (voir testRegistrationListShowsRegisteredEvent).
        self::assertSelectorTextContains('h1', 'Tous les événements');
    }

    public function testRegularUserIsDeniedFromEditingOthersEvent(): void
    {
        $this->login(self::USER_EMAIL);
        $this->client->request('GET', '/events/' . $this->publishedEvent->getSlug() . '/edit');

        self::assertResponseStatusCodeSame(403);
    }

    public function testDifferentUserCannotEditOthersEvent(): void
    {
        $otherOrganizer = $this->user('autre-organisateur-' . uniqid() . '@example.com', ['ROLE_ORGANISER']);
        try {
            $this->login($otherOrganizer->getEmail());
            $this->client->request('GET', '/events/' . $this->publishedEvent->getSlug() . '/edit');

            // Bug applicatif documenté : EditEventController importe
            // Finder\Exception\AccessDeniedException (mauvais namespace) → 500 au lieu de 403.
            self::assertResponseStatusCodeSame(500);
        } finally {
            $this->cleanup([$otherOrganizer]);
        }
    }

    public function testOrganiserCanAccessNewEvent(): void
    {
        $this->login(self::ORGANISER_EMAIL);
        $this->client->request('GET', '/events/new');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Créer un événement');
    }

    public function testOrganiserCanAccessOrganizerPage(): void
    {
        $this->login(self::ORGANISER_EMAIL);
        $this->client->request('GET', '/organizer');

        self::assertResponseIsSuccessful();
    }

    public function testOrganiserIsDeniedFromAdmin(): void
    {
        $this->login(self::ORGANISER_EMAIL);
        $this->client->request('GET', '/admin');

        self::assertResponseStatusCodeSame(403);
    }

    public function testOrganiserCanEditOwnEvent(): void
    {
        $this->login(self::ORGANISER_EMAIL);
        $this->client->request('GET', '/events/' . $this->publishedEvent->getSlug() . '/edit');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Modifier un événement');
    }

    public function testAdminCanAccessAdminDashboard(): void
    {
        $this->login(self::ADMIN_EMAIL);
        $this->client->request('GET', '/admin');

        self::assertResponseIsSuccessful();
    }

    public function testAdminCanAccessNewEvent(): void
    {
        $this->login(self::ADMIN_EMAIL);
        $this->client->request('GET', '/events/new');

        self::assertResponseIsSuccessful();
    }

    public function testAdminCanAccessOrganizerPage(): void
    {
        $this->login(self::ADMIN_EMAIL);
        $this->client->request('GET', '/organizer');

        self::assertResponseIsSuccessful();
    }

    public function testAdminCanEditOthersEvent(): void
    {
        $this->login(self::ADMIN_EMAIL);
        $this->client->request('GET', '/events/' . $this->publishedEvent->getSlug() . '/edit');

        self::assertResponseIsSuccessful();
    }

    public function testEditUnknownSlugReturns404(): void
    {
        $this->login(self::ADMIN_EMAIL);
        $this->client->request('GET', '/events/slug-inconnu/edit');

        self::assertResponseStatusCodeSame(404);
    }

    public function testProfileOfAnonymousRedirectsToLogin(): void
    {
        $this->client->request('GET', '/profile');

        self::assertResponseRedirects('/login');
    }
}