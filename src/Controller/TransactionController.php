<?php

namespace App\Controller;

use App\Entity\Transaction;
use App\Form\Search\TransactionFilterType;
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
        private readonly TransactionRepository $transactionRepository
    ) {
    }

    /**
     * Lister les opérations
     * @param PaginatorInterface $pager
     * @param int $year
     * @param string $month
     * @param int $page
     * @return Response
     */
    #[Route('/transaction/list/{year}/{month}/{page}', name: 'transaction_list', requirements: [
        "year" => "\d{4}",
        "month" => "[01]\d",
        "page" => "\d+",
    ])]
    public function list(PaginatorInterface $pager, int $year, string $month, int $page = 1): Response
    {
        // Définition du mois précédent et du mois suivant
        $currentMonth = new DateTimeImmutable("$year-$month-01");
        $nextMonth = $currentMonth->modify('+ 1 month');
        $previousMonth = $currentMonth->modify('- 1 month');

        // Formulaire de création d'opération
        $transaction = new Transaction();
        $newTransactionForm = $this->createForm(TransactionType::class, $transaction, [
            'target_url' => $this->generateUrl('transaction_create')
        ]);

        // Formulaire de filtrage
        $filterForm = $this->createForm(TransactionFilterType::class);

        // Pagination des opérations demandées
        $pagination = $pager->paginate(
            $this->transactionRepository->getAllTransactionsForMonth($year, $month),
            $page,
            100
        );

        return $this->render('transaction/list.html.twig', [
            'newTransactionForm' => $newTransactionForm->createView(),
            'pagination' => $pagination,
            'currentMonth' => $currentMonth,
            'previousMonth' => $previousMonth,
            'nextMonth' => $nextMonth,
            'filterForm' => $filterForm->createView()
        ]);
    }

    /**
     * Créer une nouvelle opération
     * @param Request $request
     * @return RedirectResponse
     */
    #[Route('transaction/create', name: 'transaction_create')]
    public function create(Request $request): RedirectResponse
    {
        // Récupération du formulaire de création
        $transaction = new Transaction();
        $newTransactionForm = $this->createForm(TransactionType::class, $transaction, [
            'target_url' => $this->generateUrl('transaction_create')
        ]);
        $newTransactionForm->handleRequest($request);

        if ($newTransactionForm->isSubmitted() && $newTransactionForm->isValid()) {
            $transaction = $newTransactionForm->getData();

            $this->em->persist($transaction);
            $this->em->flush();
        }

        return $this->redirectToRoute('transaction_list', [
            'year' => $transaction->getDate()->format('Y'),
            'month' => $transaction->getDate()->format('m')
        ]);
    }

    /**
     * Éditer l'opération $transaction
     * @param Transaction $transaction
     * @param Request $request
     * @return RedirectResponse|Response
     */
    #[Route('/transaction/edit/{id}', name: 'transaction_edit', requirements: ["id" => "\d+"])]
    public function edit(Transaction $transaction, Request $request): RedirectResponse|Response
    {
        $transactionForm = $this->createForm(TransactionType::class, $transaction);
        $transactionForm->handleRequest($request);

        if ($transactionForm->isSubmitted() && $transactionForm->isValid()) {
            /** @var Transaction $transaction */
            $transaction = $transactionForm->getData();

            $this->em->flush();

            return $this->redirectToRoute('transaction_list', [
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
     * @param Transaction $transaction
     * @return RedirectResponse
     */
    #[Route('/transaction/delete/{id}', name: 'transaction_delete', requirements: ["id" => "\d+"])]
    public function delete(Transaction $transaction): RedirectResponse
    {
        $date = $transaction->getDate();

        $this->em->remove($transaction);
        $this->em->flush();

        return $this->redirectToRoute('transaction_list', [
            'year' => $date->format('Y'),
            'month' => $date->format('m')
        ]);
    }
}
