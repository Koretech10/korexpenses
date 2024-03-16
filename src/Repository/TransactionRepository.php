<?php

namespace App\Repository;

use App\Entity\Transaction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Transaction>
 *
 * @method Transaction|null find($id, $lockMode = null, $lockVersion = null)
 * @method Transaction|null findOneBy(array $criteria, array $orderBy = null)
 * @method Transaction[]    findAll()
 * @method Transaction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transaction::class);
    }

    /**
     * Retourne toutes les opérations triées par date
     * @return QueryBuilder
     */
    public function getAllTransactions(): QueryBuilder
    {
        return $this
            ->createQueryBuilder('t')
            ->orderBy('t.date')
        ;
    }

    /**
     * Retourne toutes les opérations du mois de $month $year triées par date
     * @param int $year
     * @param string $month
     * @return QueryBuilder
     */
    public function getAllTransactionsForMonth(int $year, string $month): QueryBuilder
    {
        return $this
            ->createQueryBuilder('t')
            ->where("t.date LIKE :month")
            ->setParameter('month', "$year-$month-%")
            ->orderBy('t.date')
        ;
    }
}
