<?php

namespace App\Command;

use App\Entity\MonthlyTransaction;
use App\Entity\Transaction;
use App\Repository\MonthlyTransactionRepository;
use App\Service\MonthlyTransactionOccurrenceCalculatorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'transaction:monthly:generate',
    description: "Génère des opérations à partir d'opérations mensuelles dont le jour d'occurrence a été atteint",
)]
class TransactionGenerateCommand extends Command
{
    public function __construct(
        private readonly MonthlyTransactionRepository $monthlyTransactionRepository,
        private readonly EntityManagerInterface $em,
        private readonly MonthlyTransactionOccurrenceCalculatorService $occurrenceCalculator,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Récupérer les opérations mensuelles dont la date d'occurrence ≤ aujourd'hui
        $reachedMonthlyTransactions = $this
            ->monthlyTransactionRepository
            ->getMonthlyTransactionsWithOccurrenceReached()
            ->getQuery()
            ->getResult()
        ;

        foreach ($reachedMonthlyTransactions as $monthlyTransaction) {
            // Créer une opération à partir de cette opération mensuelle
            /** @var MonthlyTransaction $monthlyTransaction */
            $transaction = new Transaction();
            $transaction
                ->setAccount($monthlyTransaction->getAccount())
                ->setDate($monthlyTransaction->getNextOccurrence())
                ->setType($monthlyTransaction->getType())
                ->setValue($monthlyTransaction->getValue())
                ->setDescription($monthlyTransaction->getDescription())
            ;
            $this->em->persist($transaction);

            // Calculer la prochaine occurrence de l'opération mensuelle
            $monthlyTransaction->setNextOccurrence($this
                ->occurrenceCalculator
                ->calculateNextMonthlyTransactionOccurrence($monthlyTransaction));
        }

        // Mettre à jour la base de données
        $this->em->flush();
        return Command::SUCCESS;
    }
}
