<?php

namespace App\Repository;

use App\Entity\Account;
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
     * Retourne toutes les opérations mensuelles pour le compte bancaire $account triées par jour et libellé
     * @param Account $account
     * @return QueryBuilder
     */
    public function getAllMonthlyTransactionsForAccount(Account $account): QueryBuilder
    {
        return $this
            ->createQueryBuilder('mt')
            ->where('mt.account = :account')
            ->setParameter('account', $account)
            ->orderBy('mt.day', 'ASC')
            ->addOrderBy('mt.description', 'ASC')
        ;
    }

    /**
     * Retourne les opérations mensuelles filtrées selon la requête $filterQuery pour le compte bancaire $account
     * @param Account $account
     * @param array $filterQuery
     * @return QueryBuilder
     */
    public function filterMonthlyTransactionsForAccount(Account $account, array $filterQuery): QueryBuilder
    {
        $query = $this
            ->getAllMonthlyTransactionsForAccount($account)
            ->andWhere('mt.description LIKE :description')
            ->setParameter('description', '%' . $filterQuery['description'] . '%')
        ;

        // Si l'intervalle de début de date d'opération est renseigné
        if ($filterQuery['dayFrom'] !== null) {
            $query
                ->andWhere(':dayFrom <= mt.day')
                ->setParameter('dayFrom', $filterQuery['dayFrom'])
            ;
        }

        // Si l'intervalle de fin de date d'opération est renseigné
        if ($filterQuery['dayTo'] !== null) {
            $query
                ->andWhere('mt.day <= :dayTo')
                ->setParameter('dayTo', $filterQuery['dayTo'])
            ;
        }

        // Si au moins un type d'opération a été coché
        if ($filterQuery['type'] !== []) {
            $query
                ->andWhere('mt.type IN (:types)')
                ->setParameter('types', $filterQuery['type'])
            ;
        }

        // Si l'intervalle de début de valeur d'opération est renseigné
        if ($filterQuery['valueFrom'] !== null) {
            $query
                ->andWhere(':valueFrom <= mt.value')
                ->setParameter('valueFrom', $filterQuery['valueFrom'])
            ;
        }

        // Si l'intervalle de fin de valeur d'opération est renseigné
        if ($filterQuery['valueTo'] !== null) {
            $query
                ->andWhere('mt.value <= :valueTo')
                ->setParameter('valueTo', $filterQuery['valueTo'])
            ;
        }

        return $query;
    }
}
