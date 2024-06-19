<?php

namespace App\Repository;

use App\Entity\MonthlyTransaction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MonthlyTransaction>
 *
 * @method MonthlyTransaction|null find($id, $lockMode = null, $lockVersion = null)
 * @method MonthlyTransaction|null findOneBy(array $criteria, array $orderBy = null)
 * @method MonthlyTransaction[]    findAll()
 * @method MonthlyTransaction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MonthlyTransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MonthlyTransaction::class);
    }

    /**
     * Retourne toutes les opérations mensuelles triées par jour et libellé
     * @return QueryBuilder
     */
    public function getAllMonthlyTransactions(): QueryBuilder
    {
        return $this
            ->createQueryBuilder('mt')
            ->orderBy('mt.day', 'ASC')
            ->addOrderBy('mt.description', 'ASC')
        ;
    }
}
