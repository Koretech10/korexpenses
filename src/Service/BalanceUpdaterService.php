<?php

namespace App\Service;

use App\Entity\Account;
use App\Entity\Transaction;
use App\Repository\AccountRepository;
use App\Repository\TransactionRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Service permettant de mettre à jour le solde d'un compte bancaire lors d'une opération sur une transaction
 */
class BalanceUpdaterService
{
    private DateTimeImmutable $now;

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly TransactionRepository $transactionRepository,
        private readonly AccountRepository $accountRepository
    ) {
        $this->now = new DateTimeImmutable();
    }

    /**
     * Mets à jour le solde suite à la création de $transaction
     * @param Transaction $transaction
     * @return void
     */
    public function addTransaction(Transaction $transaction): void
    {
        // Ne mettre à jour le solde que si la date de la transaction n'est pas dans le futur
        if ($transaction->getDate() <= $this->now) {
            $account = $transaction->getAccount();

            // Récupération de la valeur de la transaction
            $value = $this->getValueFromTransaction($transaction);

            // Mise à jour du solde
            $account->setBalance($account->getBalance() + $value);

            $this->em->flush();
        }
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
        $oldValue = 0;
        $newValue = 0;

        // Récupération de l'ancienne valeur si la date de la transaction n'est pas dans le futur
        if ($oldTransaction->getDate() <= $this->now) {
            $oldValue = $this->getValueFromTransaction($oldTransaction);
        }

        // Récupération de la nouvelle valeur si la date de la transaction n'est pas dans le futur
        if ($newTransaction->getDate() <= $this->now) {
            $newValue = $this->getValueFromTransaction($newTransaction);
        }

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
        // Ne mettre à jour le solde que si la date de la transaction n'est pas dans le futur
        if ($transaction->getDate() <= $this->now) {
            $account = $transaction->getAccount();

            // Récupération de la valeur de la transaction
            $value = $this->getValueFromTransaction($transaction);

            // Mise à jour du solde
            $account->setBalance($account->getBalance() - $value);

            $this->em->flush();
        }
    }

    /**
     * Mets à jour la solde à partir de toutes les transactions de $account jusqu'à aujourd'hui
     * @return void
     */
    public function updateAccountsBalance(): void
    {
        foreach ($this->accountRepository->findAll() as $account) {
            $balance = 0;
            $transactions = $this
                ->transactionRepository
                ->filterTransactionsForAccount($account, [
                    'description' => '',
                    'dateFrom' => null,
                    'dateTo' => $this->now,
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
     * Retourne le solde prévu de $account en prenant en compte les $transactions fournies
     * @param Account $account
     * @param array<Transaction> $transactions
     * @return float
     */
    public function getBalanceForecast(Account $account, array $transactions): float
    {
        $balance = $account->getBalance();

        foreach ($transactions as $transaction) {
            $balance += $this->getValueFromTransaction($transaction);
        }

        return $balance;
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
