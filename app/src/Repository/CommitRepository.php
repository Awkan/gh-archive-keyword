<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Commit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class CommitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Commit::class);
    }

    /**
     * Count commits by criteria
     * 
     * @param array $criteria Criteria to apply to filter results
     * @return int The number of rows count
     */
    public function countBy(array $criteria): int
    {
        $qb = $this->createQueryBuilder('o');

        $qb->select('count(o.id)');
        foreach($criteria as $key => $value) {
            $this->addCriterion($qb, $key, $value);
        }

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Add a query criterion
     * 
     * @param string $key The key concerned by criterion
     * @param mixed $criterion The value to filter with
     */
    private function addCriterion(QueryBuilder $qb, $key, $value): void
    {
        // No criteria yet
    }
}
