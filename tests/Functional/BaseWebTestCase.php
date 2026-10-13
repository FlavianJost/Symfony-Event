<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Category;
use App\Entity\Event;
use App\Entity\Registration;
use App\Entity\User;
use App\Enum\EventStatus;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\UnitOfWork;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

abstract class BaseWebTestCase extends WebTestCase
{
    protected const ADMIN_EMAIL = 'admin@example.com';
    protected const ORGANISER_EMAIL = 'organiser@example.com';
    protected const USER_EMAIL = 'user@example.com';
    protected const PASSWORD = 'password123';

    protected ?KernelBrowser $client = null;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->client->disableReboot();
    }

    protected function tearDown(): void
    {
        $this->client = null;
        parent::tearDown();
    }

    protected function entityManager(): EntityManagerInterface
    {
        return $this->client->getContainer()->get('doctrine')->getManager();
    }

    protected function user(string $email, array $roles = ['ROLE_USER']): User
    {
        $em = $this->entityManager();
        $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);

        if ($user) {
            return $user;
        }

        $user = new User();
        $user->setUsername(strtok($email, '@'));
        $user->setEmail($email);
        $hasher = $this->client->getContainer()->get(UserPasswordHasherInterface::class);
        $user->setPassword($hasher->hashPassword($user, self::PASSWORD));
        $user->setRoles($roles);
        $user->setLastLoginAt(new \DateTimeImmutable());

        $em->persist($user);
        $em->flush();

        return $user;
    }

    protected function admin(): User
    {
        return $this->user(self::ADMIN_EMAIL, ['ROLE_ADMIN']);
    }

    protected function organiser(): User
    {
        return $this->user(self::ORGANISER_EMAIL, ['ROLE_ORGANISER']);
    }

    protected function regularUser(): User
    {
        return $this->user(self::USER_EMAIL, ['ROLE_USER']);
    }

    protected function category(): Category
    {
        $category = new Category();
        $category->setName('Catégorie test ' . uniqid());
        $category->setSlug('categorie-test-' . uniqid());

        $em = $this->entityManager();
        $em->persist($category);
        $em->flush();

        return $category;
    }

    protected function makeEvent(?User $organizer = null, bool $published = true, ?string $title = null): Event
    {
        $event = new Event();
        $event->setTitle($title ?? 'Événement test ' . uniqid());
        $event->setSlug('evenement-test-' . uniqid());
        $event->setDescription('Description de test.');
        $event->setStartAt(new \DateTimeImmutable('+1 day'));
        $event->setEndAt(new \DateTimeImmutable('+2 days'));
        $event->setCapacity(50);
        $event->setStatus($published ? EventStatus::Published : EventStatus::Draft);
        $event->setOrganizer($organizer ?? $this->user(self::ORGANISER_EMAIL, ['ROLE_ORGANISER']));
        $event->setCategory($this->category());

        $em = $this->entityManager();
        $em->persist($event);
        $em->flush();

        return $event;
    }

    /**
     * Logs in through the real login form (exercises stateless CSRF + form_login).
     */
    protected function login(string $email, string $password = self::PASSWORD): KernelBrowser
    {
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Se connecter')->form();

        $this->client->submit($form, [
            'email' => $email,
            'password' => $password,
        ]);

        return $this->client;
    }

    /**
     * Logs in relying on the security session (faster: does not go through the form).
     */
    protected function loginAs(string $email, array $roles = ['ROLE_USER']): KernelBrowser
    {
        $user = $this->user($email, $roles);
        $this->client->loginUser($user);

        return $this->client;
    }

    protected function cleanup(array $entities): void
    {
        $em = $this->entityManager();

        $managed = function (object $entity) use ($em): ?object {
            $state = $em->getUnitOfWork()->getEntityState($entity);
            if (UnitOfWork::STATE_MANAGED === $state) {
                return $entity;
            }
            if (UnitOfWork::STATE_REMOVED === $state || UnitOfWork::STATE_NEW === $state) {
                return null;
            }
            // Détaché (l'EntityManager est purgé entre deux requêtes HTTP) → re-fetch par id.
            if (!method_exists($entity, 'getId')) {
                return null;
            }

            return $em->getRepository($entity::class)->find($entity->getId());
        };

        foreach ($entities as $entity) {
            if ($entity instanceof Event) {
                foreach ($em->getRepository(Registration::class)->findBy(['event' => (object) $entity]) as $registration) {
                    $em->remove($registration);
                }
            }
            if ($entity instanceof User) {
                foreach ($em->getRepository(Registration::class)->findBy(['user' => (object) $entity]) as $registration) {
                    $em->remove($registration);
                }
            }
        }

        foreach ($entities as $entity) {
            $event = $managed($entity);
            if (null !== $event) {
                $em->remove($event);
            }
            if ($entity instanceof Event) {
                $category = $entity->getCategory();
                if ($category) {
                    $category = $managed($category);
                    if (null !== $category) {
                        $em->remove($category);
                    }
                }
            }
        }

        $em->flush();
    }
}