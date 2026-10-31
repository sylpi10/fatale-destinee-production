<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Show;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Show>
 */
class ShowRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Show::class);
    }

    /**
     * @return list<Show>
     */
    public function findPublished(): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.published = true')
            ->orderBy('s.position', 'ASC')
            ->addOrderBy('s.title', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findOnePublishedBySlug(string $slug): ?Show
    {
        return $this->findOneBy(['slug' => $slug, 'published' => true]);
    }
}
