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
     * Lister les opérations ou en créer une nouvelle
     * @param Request $request
     * @param PaginatorInterface $pager
     * @param int $page
     * @return RedirectResponse|Response
     */
    #[Route('/transaction/list/{page}', name: 'transaction_list_or_create', requirements: ["page" => "\d+"])]
    public function listOrCreate(Request $request, PaginatorInterface $pager, int $page = 1): RedirectResponse|Response
    {
        $transaction = new Transaction();
        $newTransactionForm = $this->createForm(TransactionType::class, $transaction);
        $newTransactionForm->handleRequest($request);

        if ($newTransactionForm->isSubmitted() && $newTransactionForm->isValid()) {
            $transaction = $newTransactionForm->getData();

            $this->em->persist($transaction);
            $this->em->flush();

            return $this->redirectToRoute('transaction_list_or_create');
        }

        $pagination = $pager->paginate(
            $this->transactionRepository->getAllTransactions(),
            $page,
            2
        );

        return $this->render('transaction/list_or_create.html.twig', [
            'newTransactionForm' => $newTransactionForm->createView(),
            'pagination' => $pagination
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
            $transaction = $transactionForm->getData();

            $this->em->flush();

            return $this->redirectToRoute('transaction_list_or_create');
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

        return $this->redirectToRoute('transaction_list_or_create');
    }
}
