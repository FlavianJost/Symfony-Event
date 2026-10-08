<?php

namespace App\Repository;

use App\Entity\Event;
use App\Entity\Registration;
use App\Entity\User;
use App\Enum\RegistrationStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Registration>
 */
class RegistrationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Registration::class);
    }

    public function countConfirmedByEvent(Event $event): int
    {
        return (int) $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->andWhere('r.event = :event')
            ->andWhere('r.status = :status')
            ->setParameter('event', $event)
            ->setParameter('status', RegistrationStatus::Confirmed)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findEventsByUser(User $user): array
    {
        $registrations = $this->createQueryBuilder('r')
            ->andWhere('r.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
        return array_map(fn(Registration $registration) => $registration->getEvent(), $registrations);
    }

    public function save(?Registration $registration): void
    {
        $this->getEntityManager()->flush();
    }

    public function persist(Registration $registration): void
    {
        $this->getEntityManager()->persist($registration);
    }

    public function persistandsave(Registration $registration): void
    {
        $this->save($registration);
        $this->persist($registration);
    }
}
