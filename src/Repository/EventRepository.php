<?php

namespace App\Repository;

use App\Entity\Event;
use App\Entity\User;
use App\Enum\EventStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Event>
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    public function save(Event $event): void
    {
        $this->getEntityManager()->flush();
    }

    public function persist(Event $event): void
    {
        $this->getEntityManager()->persist($event);
    }

    public function saveandpersist(Event $event): void
    {
        $this->getEntityManager()->persist($event);
        $this->getEntityManager()->flush();
    }

    public function findAll(): array
    {
        return $this->createQueryBuilder('e')
            ->getQuery()
            ->getResult();
    }

    public function findEventPublished(): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.status=:status')
            ->setParameter('status', EventStatus::Published)
            ->getQuery()
            ->getResult();
    }

    public function findEventByOrganizer(User $organizer): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.organizer=:organizer')
            ->setParameter('organizer', $organizer)
            ->getQuery()
            ->getResult();
    }
}
