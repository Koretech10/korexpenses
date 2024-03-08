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

    /**
     * Lister les opérations ou en créer une nouvelle
     * @param Request $request
     * @return RedirectResponse|Response
     */
    #[Route('/transaction/list', name: 'transaction_list_or_create')]
    public function listOrCreate(Request $request): RedirectResponse|Response
    {
        $transaction = new Transaction();
        $newTransactionForm = $this->createForm(TransactionType::class, $transaction);
        $newTransactionForm->handleRequest($request);

        if ($newTransactionForm->isSubmitted() && $newTransactionForm->isValid()) {
            $transaction = $newTransactionForm->getData();
            $debit = $newTransactionForm['debit']->getData();
            $credit = $newTransactionForm['credit']->getData();

            // Transformation du champ Débit ou Crédit en type d'opération avec une valeur
            if (!is_null($debit)) {
                $this->transformDebitIntoValue($transaction, $debit);
            }
            if (!is_null($credit)) {
                $this->transformCreditIntoValue($transaction, $credit);
            }

            $this->em->persist($transaction);
            $this->em->flush();

            return $this->redirectToRoute('transaction_list_or_create');
        }

        $transactions = $this->transactionRepository->findAll();

        return $this->render('transaction/list_or_create.html.twig', [
            'newTransactionForm' => $newTransactionForm->createView(),
            'transactions' => $transactions
        ]);
    }

    /**
     * Éditer l'opération $transaction
     * @param Transaction $transaction
     * @return Response
     */
    #[Route('/transaction/edit/{id}', name: 'transaction_edit', requirements: ["id" => "\d+"])]
    public function edit(Transaction $transaction): Response
    {
        return $this->render('list_or_create.html.twig');
    }

    /**
     * Supprimer l'opération $transaction
     * @param Transaction $transaction
     * @return RedirectResponse
     */
    #[Route('/transaction/delete/{id}', name: 'transaction_delete', requirements: ["id" => "\d+"])]
    public function delete(Transaction $transaction): RedirectResponse
    {
        return $this->redirectToRoute('transaction_list_or_create');
    }

    /**
     * Transforme le débit $value en opération de type Débit avec la valeur $value dans l'opération $transaction
     * @param Transaction $transaction
     * @param float $value
     * @return void
     */
    private function transformDebitIntoValue(Transaction $transaction, float $value): void
    {
        $transaction
            ->setType(0)
            ->setValue($value)
        ;
    }

    /**
     * Transforme le crédit $value en opération de type Crédit avec la valeur $value dans l'opération $transaction
     * @param Transaction $transaction
     * @param float $value
     * @return void
     */
    private function transformCreditIntoValue(Transaction $transaction, float $value): void
    {
        $transaction
            ->setType(1)
            ->setValue($value)
        ;
    }
}
