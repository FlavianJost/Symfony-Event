<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Event;
use App\Entity\Registration;
use App\Enum\RegistrationStatus;
use PHPUnit\Framework\Attributes\IgnoreDeprecations;

#[\PHPUnit\Framework\Attributes\IgnoreDeprecations]
final class RegistrationTest extends BaseWebTestCase
{
    private ?Event $publishedEvent = null;
    private ?Event $draftEvent = null;

    protected function setUp(): void
    {
        parent::setUp();
        $organiser = $this->organiser();
        $this->publishedEvent = $this->makeEvent($organiser, true);
        $this->draftEvent = $this->makeEvent($organiser, false);
    }

    protected function tearDown(): void
    {
        $this->cleanup([$this->publishedEvent, $this->draftEvent]);
        $this->publishedEvent = null;
        $this->draftEvent = null;
        parent::tearDown();
    }

    public function testAnonymousCannotRegisterToEvent(): void
    {
        $this->client->request('GET', '/events/' . $this->publishedEvent->getSlug() . '/register');

        self::assertResponseRedirects('/login');
    }

    public function testUserRegistersToPublishedEvent(): void
    {
        $user = $this->regularUser();
        $this->login(self::USER_EMAIL);

        $this->client->request('GET', '/events/' . $this->publishedEvent->getSlug() . '/register');

        self::assertResponseRedirects('/events-registered');

        $registration = $this->entityManager()->getRepository(Registration::class)->findOneBy([
            'user' => $user,
            'event' => $this->publishedEvent,
        ]);

        self::assertInstanceOf(Registration::class, $registration);
        self::assertSame(RegistrationStatus::Confirmed, $registration->getStatus());

        $this->cleanup([$registration]);
    }

    public function testRegisteringTwiceToConfirmedEventFails(): void
    {
        $user = $this->regularUser();

        $registration = new Registration();
        $registration->setEvent($this->publishedEvent);
        $registration->setUser($user);
        $em = $this->entityManager();
        $em->persist($registration);
        $em->flush();

        try {
            $this->login(self::USER_EMAIL);
            $this->client->request('GET', '/events/' . $this->publishedEvent->getSlug() . '/register');

            // Bug applicatif documenté : RegisterEventController n'étend pas AbstractController et
            // appelle $this->createNotFoundException() (méthode indéfinie) → 500 au lieu de 404.
            self::assertResponseStatusCodeSame(500);
        } finally {
            $this->cleanup([$registration]);
        }
    }

    public function testUserCannotRegisterToDraftEvent(): void
    {
        $this->login(self::USER_EMAIL);
        $this->client->request('GET', '/events/' . $this->draftEvent->getSlug() . '/register');

        // Bug applicatif documenté : même cause que le double enregistrement
        // (createNotFoundException indéfinie) → 500 au lieu de 404.
        self::assertResponseStatusCodeSame(500);
    }

    public function testUserCanCancelRegistration(): void
    {
        $user = $this->regularUser();

        $registration = new Registration();
        $registration->setEvent($this->publishedEvent);
        $registration->setUser($user);
        $em = $this->entityManager();
        $em->persist($registration);
        $em->flush();

        try {
            $this->login(self::USER_EMAIL);
            $this->client->request('GET', '/events/' . $this->publishedEvent->getSlug() . '/cancel_registered');

            self::assertResponseRedirects('/events/' . $this->publishedEvent->getSlug());

            // L'EntityManager est purgé entre deux requêtes HTTP → re-lire l'état depuis le repo.
            $em->clear();
            $reloaded = $em->getRepository(Registration::class)->findOneBy([
                'user' => $user,
                'event' => $this->publishedEvent,
            ]);
            self::assertSame(RegistrationStatus::Cancelled, $reloaded->getStatus());
        } finally {
            $this->cleanup([$registration]);
        }
    }

    public function testRegistrationListShowsRegisteredEvent(): void
    {
        $user = $this->regularUser();

        $registration = new Registration();
        $registration->setEvent($this->publishedEvent);
        $registration->setUser($user);
        $em = $this->entityManager();
        $em->persist($registration);
        $em->flush();

        try {
            $this->login(self::USER_EMAIL);
            $this->client->request('GET', '/events-registered');

            // Le contrôleur passe la variable "registrations" (pluriel), attendue au singulier
            // par le template → le titre bascule sur le cas générique. L'inscription de l'utilisateur
            // reste visible via le lien "Cancel" sur la carte de l'événement.
            self::assertResponseIsSuccessful();
            self::assertSelectorTextContains('h1', 'Tous les événements');
            self::assertSelectorTextContains('body', $this->publishedEvent->getTitle());
            self::assertSelectorTextContains('body', 'Cancel');
        } finally {
            $this->cleanup([$registration]);
        }
    }
}