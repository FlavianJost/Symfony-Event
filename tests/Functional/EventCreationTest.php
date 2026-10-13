<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Event;
use App\Enum\EventStatus;
use PHPUnit\Framework\Attributes\IgnoreDeprecations;
use Symfony\Component\String\Slugger\SluggerInterface;

#[\PHPUnit\Framework\Attributes\IgnoreDeprecations]
final class EventCreationTest extends BaseWebTestCase
{
    public function testAnonymousCannotCreateEvent(): void
    {
        $this->client->request('GET', '/events/new');

        self::assertResponseRedirects('/login');
    }

    public function testRegularUserCannotCreateEvent(): void
    {
        $this->login(self::USER_EMAIL);
        $this->client->request('GET', '/events/new');

        self::assertResponseStatusCodeSame(403);
    }

    public function testOrganiserCreatesEvent(): void
    {
        $this->login(self::ORGANISER_EMAIL);

        $category = $this->category();
        $title = 'Création test ' . uniqid();

        $crawler = $this->client->request('GET', '/events/new');
        self::assertResponseIsSuccessful();
        $form = $crawler->selectButton('Créer l\'événement')->form();

        $this->client->submit($form, [
            'event' => $this->eventFormParameters($title, (int) $category->getId()),
        ]);

        self::assertResponseRedirects();

        $slugger = $this->client->getContainer()->get(SluggerInterface::class);
        $expectedSlug = strtolower($slugger->slug($title)->toString());
        self::assertStringEndsWith('/events/' . $expectedSlug, $this->client->getResponse()->headers->get('Location'));

        $created = $this->entityManager()->getRepository(Event::class)->findOneBy(['slug' => $expectedSlug]);

        self::assertInstanceOf(Event::class, $created);
        self::assertSame($title, $created->getTitle());
        self::assertSame(EventStatus::Published, $created->getStatus());
        self::assertSame(self::ORGANISER_EMAIL, $created->getOrganizer()->getEmail());

        $this->cleanup([$created, $category]);
    }

    public function testOrganiserCreatesEventWithCoverImage(): void
    {
        $this->login(self::ORGANISER_EMAIL);

        $category = $this->category();
        $title = 'Conférence avec image ' . uniqid();

        $crawler = $this->client->request('GET', '/events/new');
        self::assertResponseIsSuccessful();

        $tmp = tempnam(sys_get_temp_dir(), 'cover');
        self::assertNotFalse($tmp);
        file_put_contents($tmp, base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII='
        ));

        $form = $crawler->selectButton('Créer l\'événement')->form();
        $form['event[coverImage]']->upload($tmp);

        $this->client->submit($form, [
            'event' => $this->eventFormParameters($title, (int) $category->getId()),
        ]);

        self::assertResponseRedirects();

        $slugger = $this->client->getContainer()->get(SluggerInterface::class);
        $expectedSlug = strtolower($slugger->slug($title)->toString());
        $created = $this->entityManager()->getRepository(Event::class)->findOneBy(['slug' => $expectedSlug]);

        self::assertInstanceOf(Event::class, $created);
        self::assertNotEmpty($created->getCoverImage());
        self::assertStringEndsWith('.png', $created->getCoverImage());

        $uploadedFile = 'public/uploads/events/' . $created->getCoverImage();
        self::assertFileExists($uploadedFile);

        @unlink($uploadedFile);
        $this->cleanup([$created, $category]);
    }

    public function testOrganiserCreatingInvalidEventShowsErrors(): void
    {
        $this->login(self::ORGANISER_EMAIL);

        $category = $this->category();
        $crawler = $this->client->request('GET', '/events/new');
        $form = $crawler->selectButton('Créer l\'événement')->form();

        $this->client->submit($form, [
            'event' => [
                // Titre vide → violation NotBlank (les dates restent remplies car une date vide
                // déclenche une TypeError côté app : setStartAt() attend un DateTimeImmutable).
                'title' => '',
                'description' => 'Description valide.',
                'startAt' => '2030-01-10T10:00',
                'endAt' => '2030-01-10T18:00',
                'capacity' => '30',
                'status' => 'draft',
                'category' => (string) $category->getId(),
            ],
        ]);

        self::assertResponseIsSuccessful();
        // Le thème Symfony 7.3 rend les erreurs en <li id="event_title_error1">.
        self::assertSelectorTextContains('#event_title_error1', 'This value should not be blank.');

        $this->cleanup([$category]);
    }

    public function testCreatingEventWithTamperedCsrfIsRejected(): void
    {
        $this->login(self::ORGANISER_EMAIL);

        $category = $this->category();
        $title = 'Titre CSRF ' . uniqid();

        $crawler = $this->client->request('GET', '/events/new');
        $form = $crawler->selectButton('Créer l\'événement')->form();

        $this->client->submit($form, [
            'event' => array_merge($this->eventFormParameters($title, (int) $category->getId(), 'draft'), [
                '_token' => 'jeton-falsifié',
            ]),
        ]);

        // Le token CSRF falsifié rend le formulaire invalide → simple re-rendu (200) sans erreur
        // visible (l'app n'affiche pas l'erreur CSRF racine dans ce template) et sans persistance.
        self::assertFalse($this->client->getResponse()->isRedirect());
        self::assertNull($this->entityManager()->getRepository(Event::class)->findOneBy(['title' => $title]));

        $this->cleanup([$category]);
    }

    private function eventFormParameters(string $title, int $categoryId, string $status = 'published'): array
    {
        return [
            'title' => $title,
            'description' => 'Description créée par le test.',
            'startAt' => '2030-01-10T10:00',
            'endAt' => '2030-01-10T18:00',
            'capacity' => '30',
            'status' => $status,
            'category' => (string) $categoryId,
        ];
    }
}