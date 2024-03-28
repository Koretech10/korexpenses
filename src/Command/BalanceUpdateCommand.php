<?php

namespace App\Command;

use App\Service\BalanceUpdaterService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'balance:update',
    description: "Mets à jour le solde des comptes bancaires avec toutes les transactions jusqu'à aujourd'hui",
)]
class BalanceUpdateCommand extends Command
{
    public function __construct(private readonly BalanceUpdaterService $balanceUpdater, ?string $name = null)
    {
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->balanceUpdater->updateAccountsBalance();
        return Command::SUCCESS;
    }
}
