<?php

namespace App\Controller;

use App\Entity\Account;
use App\Entity\MonthlyTransaction;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MonthlyTransactionController extends AbstractController
{
    /**
     * Lister les opérations mensuelles
     * @param Account|null $account
     * @param int $page
     */
    #[Route(
        '/transaction/list/{account}/{page}',
        name: 'monthly_transaction_list',
        requirements: ['account' => '\d+', 'page' => '\d'],
    )]
    public function list(Account $account = null, int $page = 1)
    {
    }

    /**
     * Filtrer les opérations mensuelles selon la requête dans le GET
     * @param Request $request
     * @param Account|null $account
     * @param int $page
     */
    #[Route(
        '/transaction/filter/{account}/{page}',
        name: 'monthly_transaction_filter',
        requirements: ['account' => '\d+', 'page' => '\d'],
    )]
    public function filter(Request $request, Account $account = null, int $page = 1)
    {
    }

    /**
     * Créer une opération mensuelle
     * @param Request $request
     * @param Account|null $account
     */
    #[Route(
        '/transaction/monthly/create/{account}',
        name: 'monthly_transaction_create',
        requirements: ['account' => '\d+']
    )]
    public function create(Request $request, Account $account = null)
    {
    }

    /**
     * Éditer l'opération mensuelle $monthlyTransaction
     * @param Request $request
     * @param MonthlyTransaction|null $monthlyTransaction
     */
    #[Route(
        '/transaction/monthly/edit/{id}',
        name: 'monthly_transaction_edit',
        requirements: ['id' => '\d+']
    )]
    public function edit(Request $request, MonthlyTransaction $monthlyTransaction = null)
    {
    }

    /**
     * Supprimer l'opération
     * @param MonthlyTransaction|null $monthlyTransaction
     */
    #[Route(
        '/transaction/monthly/delete/{id}',
        name: 'monthly_transaction_delete',
        requirements: ['id' => '\d+']
    )]
    public function delete(MonthlyTransaction $monthlyTransaction = null)
    {
    }
}
