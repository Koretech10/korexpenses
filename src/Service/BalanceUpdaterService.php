<?php

namespace App\Service;

use App\Entity\Transaction;
use App\Repository\AccountRepository;
use App\Repository\TransactionRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Service permettant de mettre à jour le solde d'un compte bancaire lors d'une opération sur une transaction
 */
class BalanceUpdaterService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly TransactionRepository $transactionRepository,
        private readonly AccountRepository $accountRepository
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

    /**
     * Mets à jour la solde à partir de toutes les transactions de $account jusqu'à aujourd'hui
     * @return void
     */
    public function updateAccountsBalance(): void
    {
        $now = new \DateTimeImmutable();

        foreach ($this->accountRepository->findAll() as $account) {
            $balance = 0;
            $transactions = $this
                ->transactionRepository
                ->filterTransactionsForAccount($account, [
                    'description' => '',
                    'dateFrom' => null,
                    'dateTo' => $now,
                    'type' => [],
                    'valueFrom' => null,
                    'valueTo' => null
                ])
                ->getQuery()
                ->getResult()
            ;

            // Calcul du solde à partir des transactions
            foreach ($transactions as $transaction) {
                $value = $this->getValueFromTransaction($transaction);
                $balance += $value;
            }

            // Mise à jour du solde
            $account->setBalance($balance);
        }

        $this->em->flush();
    }

    /**
     * Retourne la valeur d'une transaction selon son type
     * @param Transaction $transaction
     * @return float
     */
    private function getValueFromTransaction(Transaction $transaction): float
    {
        $transaction->getType() === 0 ?
            $value = -$transaction->getValue() :
            $value = $transaction->getValue()
        ;

        return $value;
    }
}
