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
     * Count commits by criteria.
     *
     * @param mixed[] $criteria Criteria to apply to filter results
     *
     * @return int The number of rows count
     */
    public function countBy(array $criteria): int
    {
        $qb = $this->createQueryBuilder('o');

        $qb->select('count(o.id)');
        foreach ($criteria as $key => $value) {
            $this->addCriterion($qb, $key, $value);
        }

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Add a query criterion.
     *
     * @param QueryBuilder $qb    The query builder on which apply filter
     * @param string       $key   The key concerned by criterion
     * @param mixed        $value The value to filter with
     */
    private function addCriterion(QueryBuilder $qb, string $key, $value): void
    {
        switch ($key) {
            case 'date':
                // For the moment, we got a YYYY-MM-DD date in string format, then deal with it
                $date = \DateTimeImmutable::createFromFormat('Y-m-d', $value);
                if (false === $date) {
                    throw new \RuntimeException('Date filter has not the right format. Maybe the data validation was not made (issue #9) ?');
                }
                $dateFrom = $date->setTime(0, 0);
                $dateTo = $dateFrom->add(\DateInterval::createFromDateString('+1 day'));

                $qb->andWhere($qb->expr()->gte('o.createdAt', ':' . $key . '_from_value'));
                $qb->andWhere($qb->expr()->lt('o.createdAt', ':' . $key . '_to_value'));
                $qb->setParameter($key . '_from_value', $dateFrom);
                $qb->setParameter($key . '_to_value', $dateTo);
                break;
            case 'keyword':
                $qb->andWhere($qb->expr()->like('o.message', ':' . $key . '_value'));
                $qb->setParameter($key . '_value', '%' . $value . '%');
                break;
        }
    }
}
