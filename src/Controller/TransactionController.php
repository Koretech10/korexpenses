<?php

namespace App\Controller;

use App\Entity\Transaction;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TransactionController extends AbstractController
{
    #[Route('/transaction', name: 'transaction_index_or_create')]
    public function listOrCreate(): Response
    {
        return $this->render('transaction/index_or_create.html.twig');
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
}
