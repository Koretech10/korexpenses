<?php

declare(strict_types=1);

namespace App\Twig;

use App\Entity\MonthlyTransaction;
use App\Entity\Transaction;
use Twig\Extension\AbstractExtension;
use Twig\TwigTest;

class AppExtension extends AbstractExtension
{
    public function getTests(): array
    {
        return [
            new TwigTest('transaction', [$this, 'isTransaction']),
            new TwigTest('monthlytransaction', [$this, 'isMonthlyTransaction']),
        ];
    }

    /**
     * Teste si l'objet $object est une instance de la classe Transaction
     * @param $object
     * @return bool
     */
    public function isTransaction($object): bool
    {
        return $object === Transaction::class;
    }

    /**
     * Teste si l'objet $object est une instance de la classe MonthlyTransaction
     * @param $object
     * @return bool
     */
    public function isMonthlyTransaction($object): bool
    {
        return $object === MonthlyTransaction::class;
    }
}
