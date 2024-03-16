<?php

namespace App\Controller;

use App\Entity\Transaction;
use App\Form\TransactionType;
use App\Repository\TransactionRepository;
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
     * @param Request $request
     * @param PaginatorInterface $pager
     * @param int $page
     * @return RedirectResponse|Response
     */
    #[Route('/transaction/list/{page}', name: 'transaction_list', requirements: ["page" => "\d+"])]
    public function list(Request $request, PaginatorInterface $pager, int $page = 1): RedirectResponse|Response
    {
        // Formulaire de création d'opération
        $transaction = new Transaction();
        $newTransactionForm = $this->createForm(TransactionType::class, $transaction, [
            'target_url' => $this->generateUrl('transaction_create')
        ]);
        $newTransactionForm->handleRequest($request);

        // Pagination des opérations demandées
        $pagination = $pager->paginate(
            $this->transactionRepository->getAllTransactions(),
            $page,
            100
        );

        return $this->render('transaction/list.html.twig', [
            'newTransactionForm' => $newTransactionForm->createView(),
            'pagination' => $pagination
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

        return $this->redirectToRoute('transaction_list');
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
            $transaction = $transactionForm->getData();

            $this->em->flush();

            return $this->redirectToRoute('transaction_list');
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
        $this->em->remove($transaction);
        $this->em->flush();

        return $this->redirectToRoute('transaction_list');
    }
}
