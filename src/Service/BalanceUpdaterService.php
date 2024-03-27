<?php

namespace App\Service;

use App\Entity\Transaction;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Service permettant de mettre à jour le solde d'un compte bancaire lors d'une opération sur une transaction
 */
class BalanceUpdaterService
{
    public function __construct(
        private readonly EntityManagerInterface $em
    ) {
    }

    /**
     * Mets à jour le solde suite à la création de $transaction
     * @param Transaction $transaction
     * @return void
     */
    public function addTransaction(Transaction $transaction): void
    {
        $account = $transaction->getAccount();

        $transaction->getType() === 0 ?
            $account->setBalance($account->getBalance() - $transaction->getValue()) :
            $account->setBalance($account->getBalance() + $transaction->getValue())
        ;

        $this->em->flush();
    }

    /**
     * Mets à jour le solde suite à la modification de $transaction
     * @param Transaction $oldTransaction
     * @param Transaction $newTransaction
     * @return void
     */
    public function updateTransaction(Transaction $oldTransaction, Transaction $newTransaction): void
    {
        $account = $oldTransaction->getAccount();

        // Récupération de l'ancienne valeur
        $oldTransaction->getType() === 0 ?
            $oldValue = -$oldTransaction->getValue() :
            $oldValue = $oldTransaction->getValue()
        ;

        // Récupération de la nouvelle valeur
        $newTransaction->getType() === 0 ?
            $newValue = -$newTransaction->getValue() :
            $newValue = $newTransaction->getValue()
        ;

        // Mise à jour du solde avec le delta
        $account->setBalance($account->getBalance() + ($newValue - $oldValue));

        $this->em->flush();
    }

    /**
     * Mets à jour le solde suite à la suppression de $transaction
     * @param Transaction $transaction
     * @return void
     */
    public function removeTransaction(Transaction $transaction): void
    {
        $account = $transaction->getAccount();

        $transaction->getType() === 0 ?
            $account->setBalance($account->getBalance() + $transaction->getValue()) :
            $account->setBalance($account->getBalance() - $transaction->getValue())
        ;

        $this->em->flush();
    }
}
