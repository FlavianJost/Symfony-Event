<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Event;
use PHPUnit\Framework\Attributes\IgnoreDeprecations;

#[\PHPUnit\Framework\Attributes\IgnoreDeprecations]
final class EventEditTest extends BaseWebTestCase
{
    private ?Event $event = null;
    private ?string $originalTitle = null;
    private ?string $originalSlug = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->event = $this->makeEvent($this->organiser());
        $this->originalTitle = $this->event->getTitle();
        $this->originalSlug = $this->event->getSlug();
    }

    protected function tearDown(): void
    {
        $this->cleanup([$this->event]);
        $this->event = null;
        parent::tearDown();
    }

    public function testAnonymousIsRedirectedToLogin(): void
    {
        $this->client->request('GET', '/events/' . $this->event->getSlug() . '/edit');

        self::assertResponseRedirects('/login');
    }

    public function testNonOwnerRegularUserIsDenied(): void
    {
        $this->login(self::USER_EMAIL);
        $this->client->request('GET', '/events/' . $this->event->getSlug() . '/edit');

        self::assertResponseStatusCodeSame(403);
    }

    public function testCreatorSeesEditPageWithPrefilledValues(): void
    {
        $this->login(self::ORGANISER_EMAIL);
        $crawler = $this->client->request('GET', '/events/' . $this->event->getSlug() . '/edit');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Modifier un événement');
        self::assertSame($this->originalTitle, $crawler->filter('#event_title')->attr('value'));
    }

    public function testCreatorEditsEventAndGetsNewSlug(): void
    {
        $oldSlug = $this->event->getSlug();
        $newTitle = 'Titre modifié ' . uniqid();

        $this->login(self::ORGANISER_EMAIL);
        $crawler = $this->client->request('GET', '/events/' . $oldSlug . '/edit');
        $form = $crawler->selectButton('Modifié l\'événement')->form();

        $this->client->submit($form, [
            'event' => [
                'title' => $newTitle,
                'description' => 'Nouvelle description modifiée.',
                'startAt' => '2031-05-05T09:00',
                'endAt' => '2031-05-05T17:00',
                'capacity' => '120',
                'status' => 'cancelled',
                'category' => (string) $this->event->getCategory()->getId(),
            ],
        ]);

        self::assertResponseRedirects();

        $location = $this->client->getResponse()->headers->get('Location');

        $em = $this->entityManager();
        $em->clear();
        $reloaded = $em->getRepository(Event::class)->find($this->event->getId());

        self::assertSame($newTitle, $reloaded->getTitle());
        self::assertNotSame($oldSlug, $reloaded->getSlug());
        self::assertSame('/events/' . $reloaded->getSlug(), $location);
    }

    public function testAdminCanEditOthersEvent(): void
    {
        $this->login(self::ADMIN_EMAIL);
        $this->client->request('GET', '/events/' . $this->event->getSlug() . '/edit');

        self::assertResponseIsSuccessful();
    }

    public function testInvalidEditShowsErrors(): void
    {
        $this->login(self::ORGANISER_EMAIL);

        $crawler = $this->client->request('GET', '/events/' . $this->event->getSlug() . '/edit');
        $form = $crawler->selectButton('Modifié l\'événement')->form();

        $this->client->submit($form, [
            'event' => [
                'title' => 'Titre inchangé (valide)',
                'description' => 'Description.',
                // endAt antérieure à startAt → violation validatesDates (contrôleur de callback).
                'startAt' => '2031-05-05T18:00',
                'endAt' => '2031-05-05T09:00',
                'capacity' => '30',
                'status' => 'draft',
                'category' => (string) $this->event->getCategory()->getId(),
            ],
        ]);

        self::assertResponseIsSuccessful();
        // Le thème Symfony 7.3 rend les erreurs en <li id="event_endAt_error1">.
        self::assertSelectorTextContains('#event_endAt_error1', 'La date de fin doit être postérieure à la date de début.');
    }

    public function testEditWithTamperedCsrfDoesNotPersistChanges(): void
    {
        $this->login(self::ORGANISER_EMAIL);

        $crawler = $this->client->request('GET', '/events/' . $this->event->getSlug() . '/edit');
        $form = $crawler->selectButton('Modifié l\'événement')->form();

        $this->client->submit($form, [
            'event' => [
                '_token' => 'jeton-falsifié',
                'title' => 'Titre piraté',
                'description' => 'Description.',
                'startAt' => '2031-05-05T09:00',
                'endAt' => '2031-05-05T17:00',
                'capacity' => '10',
                'status' => 'draft',
                'category' => (string) $this->event->getCategory()->getId(),
            ],
        ]);

        // Token CSRF falsifié → formulaire invalide, ré-envoi du formulaire sans modification.
        self::assertFalse($this->client->getResponse()->isRedirect());

        $em = $this->entityManager();
        $em->clear();
        $reloaded = $em->getRepository(Event::class)->find($this->event->getId());

        self::assertSame($this->originalTitle, $reloaded->getTitle());
        self::assertSame($this->originalSlug, $reloaded->getSlug());
    }
}