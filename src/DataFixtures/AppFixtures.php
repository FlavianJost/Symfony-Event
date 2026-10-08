<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Event;
use App\Entity\Registration;
use App\Entity\User;
use App\Enum\EventStatus;
use App\Enum\RegistrationStatus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private readonly UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $admin = $this->user($manager, 'admin', 'admin@example.com', 'password123', ['ROLE_ADMIN']);
        $organiser = $this->user($manager, 'organiser', 'organiser@example.com', 'password123', ['ROLE_ORGANISER']);
        $flavianj = $this->user($manager, 'flavianj', 'fjost@example.com', 'password123', ['ROLE_ADMIN']);
        $user = $this->user($manager, 'user', 'user@example.com', 'password123', ['ROLE_USER']);

        $category1 = $this->Category($manager, 'Category 1');
        $category2 = $this->Category($manager, 'Category 2');
        $category3 = $this->Category($manager, 'Category 3');

        $event1 = $this->Event($manager, 'Conférence Symfony 2026', $category1, $organiser, 'Une conférence dédiée à Symfony et ses bonnes pratiques.', new \DateTimeImmutable('+7 days'), new \DateTimeImmutable('+7 days +4 hours'), 100, EventStatus::Published);
        $event2 = $this->Event($manager, 'Workshop Doctrine', $category2, $organiser, 'Atelier pratique sur Doctrine ORM et les fixtures.', new \DateTimeImmutable('+14 days'), new \DateTimeImmutable('+14 days +3 hours'), 30, EventStatus::Published);
        $event3 = $this->Event($manager, 'Meetup PHP', $category3, $admin, 'Rencontre entre développeurs PHP de la région.', new \DateTimeImmutable('+30 days'), new \DateTimeImmutable('+30 days +2 hours'), 50, EventStatus::Draft);

        $this->Registration($manager, $flavianj, $event1);
        $this->Registration($manager, $user, $event1);
        $this->Registration($manager, $admin, $event1);
        $this->Registration($manager, $flavianj, $event2);
        $this->Registration($manager, $user, $event2);
        $this->Registration($manager, $organiser, $event3);

        $manager->flush();
    }

    private function user(ObjectManager $manager, string $username, string $email, string $password, array $roles): User
    {
        $user = new User();
        $user->setUsername($username);
        $user->setEmail($email);
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));
        $user->setRoles($roles);
        $manager->persist($user);
        return $user;
    }

    private function Category(ObjectManager $manager, string $name): Category
    {
        $category = new Category();
        $category->setName($name);
        $category->setSlug($this->slugify($name));
        $manager->persist($category);
        return $category;
    }

    private function Event(ObjectManager $manager, string $name, Category $category, User $organizer, string $description, \DateTimeImmutable $startAt, \DateTimeImmutable $endAt, int $capacity, EventStatus $status): Event
    {
        $event = new Event();
        $event->setTitle($name);
        $event->setSlug($this->slugify($name));
        $event->setDescription($description);
        $event->setStartAt($startAt);
        $event->setEndAt($endAt);
        $event->setCapacity($capacity);
        $event->setStatus($status);
        $event->setOrganizer($organizer);
        $event->setCategory($category);
        $manager->persist($event);
        return $event;
    }

    private function slugify(string $value): string
    {
        return strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '-', $value), '-'));
    }

    private function Registration(ObjectManager $manager, User $user, Event $event): Registration
    {
        $registration = new Registration();
        $registration->setUser($user);
        $registration->setEvent($event);
        $manager->persist($registration);
        return $registration;
    }
}
