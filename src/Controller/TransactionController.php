<?php

namespace App\Controller;

use App\Entity\Transaction;
use App\Form\TransactionType;
use App\Repository\TransactionRepository;
use Doctrine\ORM\EntityManagerInterface;
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

    #[Route('/transaction', name: 'transaction_index_or_create')]
    public function listOrCreate(Request $request): Response
    {
        $transaction = new Transaction();
        $newTransactionForm = $this->createForm(TransactionType::class, $transaction);
        $newTransactionForm->handleRequest($request);

        if ($newTransactionForm->isSubmitted() && $newTransactionForm->isValid()) {
            $transaction = $newTransactionForm->getData();
            $debit = $newTransactionForm['debit']->getData();
            $credit = $newTransactionForm['credit']->getData();

            if (!is_null($debit)) {
                $this->transformDebitIntoValue($transaction, $debit);
            }
            if (!is_null($credit)) {
                $this->transformCreditIntoValue($transaction, $credit);
            }

            $this->em->persist($transaction);
            $this->em->flush();
        }

        $transactions = $this->transactionRepository->findAll();

        return $this->render('transaction/index_or_create.html.twig', [
            'newTransactionForm' => $newTransactionForm->createView(),
            'transactions' => $transactions
        ]);
    }

    #[Route('/transaction/edit/{id}', name: 'transaction_edit', requirements: ["id" => "\d+"])]
    public function edit(Transaction $transaction): Response
    {
        return $this->render('transaction/index_or_create.html.twig');
    }

    #[Route('/transaction/delete/{id}', name: 'transaction_delete', requirements: ["id" => "\d+"])]
    public function delete(Transaction $transaction): RedirectResponse
    {
        return $this->redirectToRoute('transaction_index_or_create');
    }

    private function transformDebitIntoValue(Transaction $transaction, float $value): void
    {
        $transaction
            ->setType(0)
            ->setValue($value)
        ;
    }

    private function transformCreditIntoValue(Transaction $transaction, float $value): void
    {
        $transaction
            ->setType(1)
            ->setValue($value)
        ;
    }
}
