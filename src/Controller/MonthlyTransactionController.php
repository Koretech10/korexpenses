<?php

namespace App\Controller;

use App\Entity\Account;
use App\Entity\MonthlyTransaction;
use App\Form\MonthlyTransactionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MonthlyTransactionController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
    }

    /**
     * Lister les opérations mensuelles
     * @param Account|null $account
     * @param int $page
     * @return RedirectResponse|Response
     */
    #[Route(
        '/transaction/monthly/list/{account}/{page}',
        name: 'monthly_transaction_list',
        requirements: ['account' => '\d+', 'page' => '\d'],
    )]
    public function list(Account $account = null, int $page = 1): RedirectResponse|Response
    {
        // Vérification de l'existence du compte bancaire demandé
        if (!$account) {
            $this->addFlash('danger', "Ce compte n'existe pas.");
            return $this->redirectToRoute('account_list');
        }

        // Formulaire de création d'opération mensuelle
        $monthlyTransaction = new MonthlyTransaction();
        $newMonthlyTransactionForm = $this->createForm(MonthlyTransactionType::class, $monthlyTransaction, [
            'target_url' => $this->generateUrl('monthly_transaction_create', [
                'account' => $account->getId(),
            ])
        ]);

        return $this->render('monthly_transaction/list.html.twig', [
            'newMonthlyTransactionForm' => $newMonthlyTransactionForm->createView(),
        ]);
    }

    /**
     * Filtrer les opérations mensuelles selon la requête dans le GET
     * @param Request $request
     * @param Account|null $account
     * @param int $page
     */
    #[Route(
        '/transaction/monthly/filter/{account}/{page}',
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
     * @return RedirectResponse
     */
    #[Route(
        '/transaction/monthly/create/{account}',
        name: 'monthly_transaction_create',
        requirements: ['account' => '\d+']
    )]
    public function create(Request $request, Account $account = null): RedirectResponse
    {
        // Vérification de l'existence du compte bancaire demandé
        if (!$account) {
            $this->addFlash('danger', "Ce compte n'existe pas.");
            return $this->redirectToRoute('account_list');
        }

        // Récupération de formulaire de création
        $monthlyTransaction = new MonthlyTransaction();
        $monthlyTransaction->setAccount($account);
        $newMonthlyTransactionForm = $this->createForm(MonthlyTransactionType::class, $monthlyTransaction, [
            'target_url' => $this->generateUrl('monthly_transaction_create', [
                'account' => $account->getId(),
            ])
        ]);
        $newMonthlyTransactionForm->handleRequest($request);

        if ($newMonthlyTransactionForm->isSubmitted() && $newMonthlyTransactionForm->isValid()) {
            $monthlyTransaction = $newMonthlyTransactionForm->getData();

            $this->em->persist($monthlyTransaction);
            $this->em->flush();
            $this->addFlash('success', "L'opération mensuelle a été créée avec succès.");
        }

        return $this->redirectToRoute('monthly_transaction_list', [
            'account' => $account->getId(),
        ]);
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
