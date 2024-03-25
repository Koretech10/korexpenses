<?php

namespace App\Repository;

use App\Entity\Account;
use App\Entity\Transaction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
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

    /**
     * Retourne toutes les opérations du compte bancaire $account pour le mois $month $year triées par date
     * @param Account $account
     * @param int $year
     * @param string $month
     * @return QueryBuilder
     */
    public function getAllTransactionsForAccountAndMonth(Account $account, int $year, string $month): QueryBuilder
    {
        return $this
            ->createQueryBuilder('t')
            ->where('t.account = :account')
            ->andWhere('t.date LIKE :month')
            ->setParameters(new ArrayCollection([
                new Parameter('account', $account),
                new Parameter('month', "$year-$month-%")
            ]))
            ->orderBy('t.date')
        ;
    }

    /**
     * Retourne les opérations filtrées selon la requête $filterQuery
     * @param array $filterQuery
     * @return QueryBuilder
     */
    public function filterTransactions(array $filterQuery): QueryBuilder
    {
        $query = $this
            ->createQueryBuilder('t')
            ->where('t.description LIKE :description')
            ->setParameter('description', '%' . $filterQuery['description'] . '%')
        ;

        // Si l'intervalle de début de date d'opération est renseigné
        if ($filterQuery['dateFrom'] !== null) {
            $query
                ->andWhere(':dateFrom <= t.date')
                ->setParameter('dateFrom', $filterQuery['dateFrom'])
            ;
        }

        // Si l'intervalle de fin de date d'opération est renseigné
        if ($filterQuery['dateTo'] !== null) {
            $query
                ->andWhere('t.date <= :dateTo')
                ->setParameter('dateTo', $filterQuery['dateTo'])
            ;
        }

        // Si au moins un type d'opération a été coché
        if ($filterQuery['type'] !== []) {
            $query
                ->andWhere('t.type IN (:types)')
                ->setParameter('types', $filterQuery['type'])
            ;
        }

        // Si l'intervalle de début de valeur d'opération est renseigné
        if ($filterQuery['valueFrom'] !== null) {
            $query
                ->andWhere(':valueFrom <= t.value')
                ->setParameter('valueFrom', $filterQuery['valueFrom'])
            ;
        }

        // Si l'intervalle de fin de valeur d'opération est renseigné
        if ($filterQuery['valueTo'] !== null) {
            $query
                ->andWhere('t.value <= :valueTo')
                ->setParameter('valueTo', $filterQuery['valueTo'])
            ;
        }

        return $query
            ->orderBy('t.date', 'DESC')
            ->addOrderBy('t.id', 'DESC')
        ;
    }
}
