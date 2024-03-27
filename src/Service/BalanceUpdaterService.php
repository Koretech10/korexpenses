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
