<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Company;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Company>
 */
class CompanyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Company::class);
    }

    /**
     * The single company row, created empty the first time.
     */
    public function get(): Company
    {
        $company = $this->findOneBy([], ['id' => 'ASC']);

        if (null === $company) {
            $company = new Company();
            $this->getEntityManager()->persist($company);
            $this->getEntityManager()->flush();
        }

        return $company;
    }
}
