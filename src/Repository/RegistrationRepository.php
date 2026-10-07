<?php

namespace App\Repository;

use App\Entity\Event;
use App\Entity\Registration;
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
}
