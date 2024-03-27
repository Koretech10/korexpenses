<?php

namespace App\Controller;

use App\Entity\Account;
use App\Entity\Transaction;
use App\Form\Filter\TransactionFilterType;
use App\Form\TransactionType;
use App\Repository\TransactionRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TransactionController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly TransactionRepository $transactionRepository,
        private readonly PaginatorInterface $pager
    ) {
    }

    /**
     * Lister les opérations
     * @param int $year
     * @param string $month
     * @param Account|null $account
     * @param int $page
     * @return Response
     */
    #[Route('/transaction/list/{account}/{year}/{month}/{page}', name: 'transaction_list', requirements: [
        "account" => "\d+",
        "year" => "\d{4}",
        "month" => "[01]\d",
        "page" => "\d+",
    ])]
    public function list(int $year, string $month, Account $account = null, int $page = 1): Response
    {
        // Vérification de l'existence du compte bancaire demandé
        if (!$account) {
            $this->addFlash('danger', "Ce compte n'existe pas.");
            return $this->redirectToRoute('account_list');
        }

        // Définition du mois précédent et du mois suivant
        $currentMonth = new DateTimeImmutable("$year-$month-01");
        $nextMonth = $currentMonth->modify('+ 1 month');
        $previousMonth = $currentMonth->modify('- 1 month');

        // Formulaire de création d'opération
        $transaction = new Transaction();
        $newTransactionForm = $this->createForm(TransactionType::class, $transaction, [
            'target_url' => $this->generateUrl('transaction_create', [
                'account' => $account->getId()
            ])
        ]);

        // Formulaire de filtrage
        $filterForm = $this->createForm(TransactionFilterType::class, null, [
            'target_url' => $this->generateUrl('transaction_filter', [
                'account' => $account->getId()
            ])
        ]);

        // Pagination des opérations demandées
        $pagination = $this->pager->paginate(
            $this->transactionRepository->getAllTransactionsForAccountAndMonth($account, $year, $month),
            $page,
            100
        );

        return $this->render('transaction/list.html.twig', [
            'newTransactionForm' => $newTransactionForm->createView(),
            'pagination' => $pagination,
            'currentMonth' => $currentMonth,
            'previousMonth' => $previousMonth,
            'nextMonth' => $nextMonth,
            'account' => $account,
            'filterForm' => $filterForm->createView()
        ]);
    }

    /**
     * Filtrer les opérations selon la requête dans le GET
     * @param Request $request
     * @param Account|null $account
     * @param int $page
     * @return Response|RedirectResponse
     */
    #[Route('/transaction/filter/{account}/{page}', name: 'transaction_filter', requirements: [
        "account" => "\d+",
        "page" => "\d+"
    ])]
    public function filter(Request $request, Account $account = null, int $page = 1): Response|RedirectResponse
    {
        // Vérification de l'existence du compte bancaire demandé
        if (!$account) {
            $this->addFlash('danger', "Ce compte n'existe pas.");
            return $this->redirectToRoute('account_list');
        }

        // Formulaire de filtrage
        $filterForm = $this->createForm(TransactionFilterType::class, null, [
            'target_url' => $this->generateUrl('transaction_filter', [
                'account' => $account->getId()
            ])
        ]);
        $filterForm->handleRequest($request);

        // Initialisation des variables si formulaire non valide
        $pagination = $this->pager->paginate([], $page, 100);
        $groupedTransactions = [];

        if ($filterForm->isSubmitted() && $filterForm->isValid()) {
            $filterQuery = $filterForm->getData();

            // Pagination des opérations demandées
            $pagination = $this->pager->paginate(
                $this->transactionRepository->filterTransactionsForAccount($account, $filterQuery),
                $page,
                100
            );

            // Regrouper par mois
            $groupedTransactions = $this->regroupTransactionsByMonth($pagination->getItems());
        }

        // Formulaire de création d'opération
        $transaction = new Transaction();
        $newTransactionForm = $this->createForm(TransactionType::class, $transaction, [
            'target_url' => $this->generateUrl('transaction_create', [
                'account' => $account->getId()
            ])
        ]);

        return $this->render('transaction/filter.html.twig', [
            'pagination' => $pagination,
            'groupedTransactions' => $groupedTransactions,
            'filterForm' => $filterForm->createView(),
            'newTransactionForm' => $newTransactionForm->createView(),
            'account' => $account
        ]);
    }

    /**
     * Créer une nouvelle opération
     * @param Request $request
     * @param Account|null $account
     * @return RedirectResponse
     */
    #[Route('transaction/create/{account}', name: 'transaction_create', requirements: ["account" => "\d+"])]
    public function create(Request $request, Account $account = null): RedirectResponse
    {
        // Vérification de l'existence du compte bancaire demandé
        if (!$account) {
            $this->addFlash('danger', "Ce compte n'existe pas.");
            return $this->redirectToRoute('account_list');
        }

        // Récupération du formulaire de création
        $transaction = new Transaction();
        $transaction->setAccount($account);
        $newTransactionForm = $this->createForm(TransactionType::class, $transaction, [
            'target_url' => $this->generateUrl('transaction_create', [
                'account' => $account->getId()
            ])
        ]);
        $newTransactionForm->handleRequest($request);

        if ($newTransactionForm->isSubmitted() && $newTransactionForm->isValid()) {
            $transaction = $newTransactionForm->getData();

            $this->em->persist($transaction);
            $this->em->flush();
            $this->addFlash('success', "L'opération a été créée avec succès.");
        }

        return $this->redirectToRoute('transaction_list', [
            'account' => $account->getId(),
            'year' => $transaction->getDate()->format('Y'),
            'month' => $transaction->getDate()->format('m')
        ]);
    }

    /**
     * Éditer l'opération $transaction
     * @param Request $request
     * @param Transaction|null $transaction
     * @return RedirectResponse|Response
     */
    #[Route('/transaction/edit/{id}', name: 'transaction_edit', requirements: ["id" => "\d+"])]
    public function edit(Request $request, Transaction $transaction = null): RedirectResponse|Response
    {
        // Vérification de l'existence de la transaction demandée
        if (!$transaction) {
            $this->addFlash('danger', "Cette transaction n'existe pas.");
            return $this->redirectToRoute('account_list');
        }

        $transactionForm = $this->createForm(TransactionType::class, $transaction);
        $transactionForm->handleRequest($request);

        if ($transactionForm->isSubmitted() && $transactionForm->isValid()) {
            /** @var Transaction $transaction */
            $transaction = $transactionForm->getData();

            $this->em->flush();
            $this->addFlash('success', "Opération modifiée avec succès.");

            return $this->redirectToRoute('transaction_list', [
                'account' => $transaction->getAccount()->getId(),
                'year' => $transaction->getDate()->format('Y'),
                'month' => $transaction->getDate()->format('m')
            ]);
        }

        return $this->render('transaction/edit.html.twig', [
            'transactionForm' => $transactionForm->createView(),
            'transaction' => $transaction
        ]);
    }

    /**
     * Supprimer l'opération $transaction
     * @param Transaction|null $transaction
     * @return RedirectResponse
     */
    #[Route('/transaction/delete/{id}', name: 'transaction_delete', requirements: ["id" => "\d+"])]
    public function delete(Transaction $transaction = null): RedirectResponse
    {
        // Vérification de l'existence de la transaction demandée
        if (!$transaction) {
            $this->addFlash('danger', "Cette transaction n'existe pas.");
            return $this->redirectToRoute('account_list');
        }

        $date = $transaction->getDate();
        $account = $transaction->getAccount();

        $this->em->remove($transaction);
        $this->em->flush();
        $this->addFlash('success', "Opération supprimée avec succès.");

        return $this->redirectToRoute('transaction_list', [
            'account' => $account->getId(),
            'year' => $date->format('Y'),
            'month' => $date->format('m')
        ]);
    }

    /**
     * Retourner un array regroupant les opérations par mois et année
     * @param array $transactions
     * @return array
     */
    private function regroupTransactionsByMonth(array $transactions): array
    {
        $groupedTransactions = [];

        foreach ($transactions as $transaction) {
            /** @var Transaction $transaction */
            $year = $transaction->getDate()->format('Y');
            $month = $transaction->getDate()->format('m');

            if (!isset($groupedTransactions[$year])) {
                $groupedTransactions[$year] = [];
            }

            if (!isset($groupedTransactions[$year][$month])) {
                $groupedTransactions[$year][$month] = [];
            }

            $groupedTransactions[$year][$month][] = $transaction;
        }

        return $groupedTransactions;
    }
}
