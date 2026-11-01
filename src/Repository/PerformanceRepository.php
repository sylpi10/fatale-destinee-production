<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Performance;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Performance>
 */
class PerformanceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Performance::class);
    }

    /**
     * Dates from today on, of published shows, soonest first (Agenda page).
     *
     * @return list<Performance>
     */
    public function findUpcoming(\DateTimeImmutable $today): array
    {
        return $this->createQueryBuilder('performance')
            // "member" is a DQL keyword (MEMBER OF): the aliases are prefixed.
            ->addSelect('theaterShow', 'showMember')
            ->join('performance.show', 'theaterShow')
            ->leftJoin('theaterShow.member', 'showMember')
            ->andWhere('theaterShow.published = true')
            ->andWhere('performance.date >= :today')
            ->setParameter('today', $today, Types::DATE_IMMUTABLE)
            ->orderBy('performance.date', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
