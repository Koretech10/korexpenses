<?php

namespace App\Controller;

use App\Entity\Account;
use App\Entity\MonthlyTransaction;
use App\Form\Filter\MonthlyTransactionFilterType;
use App\Form\MonthlyTransactionType;
use App\Repository\MonthlyTransactionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MonthlyTransactionController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly PaginatorInterface $pager,
        private readonly MonthlyTransactionRepository $monthlyTransactionRepository,
    ) {
    }

    /**
     * Lister les opérations mensuelles
     * @param Request $request
     * @param Account|null $account
     * @param int $page
     * @return RedirectResponse|Response
     */
    #[Route(
        '/transaction/monthly/list/{account}/{page}',
        name: 'monthly_transaction_list',
        requirements: ['account' => '\d+', 'page' => '\d'],
    )]
    public function list(Request $request, Account $account = null, int $page = 1): RedirectResponse|Response
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

        // Formulaire de filtrage
        $filterForm = $this->createForm(MonthlyTransactionFilterType::class);
        $filterForm->handleRequest($request);

        // Choix de la requête selon la présence de filtrage ou pas
        $monthlyTransactionQuery = $this->monthlyTransactionRepository->getAllMonthlyTransactionsForAccount($account);
        if ($filterForm->isSubmitted() && $filterForm->isValid()) {
            $monthlyTransactionQuery = $this->monthlyTransactionRepository->filterMonthlyTransactionsForAccount(
                $account,
                $filterForm->getData()
            );
        }

        // Pagination des opérations mensuelles demandées
        $pagination = $this->pager->paginate(
            $monthlyTransactionQuery,
            $page,
            100
        );

        return $this->render('monthly_transaction/list.html.twig', [
            'newMonthlyTransactionForm' => $newMonthlyTransactionForm->createView(),
            'filterForm' => $filterForm->createView(),
            'pagination' => $pagination,
            'account' => $account,
        ]);
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
     * @return RedirectResponse|Response
     */
    #[Route(
        '/transaction/monthly/edit/{id}',
        name: 'monthly_transaction_edit',
        requirements: ['id' => '\d+']
    )]
    public function edit(Request $request, MonthlyTransaction $monthlyTransaction = null): RedirectResponse|Response
    {
        // Vérification de l'existence de l'opération mensuelle demandée
        if (!$monthlyTransaction) {
            $this->addFlash('danger', "Cette opération mensuelle n'existe pas.");
            return $this->redirectToRoute('account_list');
        }

        $monthlyTransactionForm = $this->createForm(MonthlyTransactionType::class, $monthlyTransaction);
        $monthlyTransactionForm->handleRequest($request);

        if ($monthlyTransactionForm->isSubmitted() && $monthlyTransactionForm->isValid()) {
            /** @var MonthlyTransaction $monthlyTransaction */
            $monthlyTransaction = $monthlyTransactionForm->getData();

            $this->em->flush();
            $this->addFlash('success', 'Opération mensuelle modifiée avec succès.');

            return $this->redirectToRoute('monthly_transaction_list', [
                'account' => $monthlyTransaction->getAccount()->getId(),
            ]);
        }

        return $this->render('monthly_transaction/edit.html.twig', [
            'monthlyTransaction' => $monthlyTransaction,
            'monthlyTransactionForm' => $monthlyTransactionForm->createView(),
        ]);
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
