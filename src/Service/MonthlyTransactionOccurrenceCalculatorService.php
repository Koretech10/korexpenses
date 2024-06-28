<?php

namespace App\Service;

use App\Entity\MonthlyTransaction;
use DateTime;

/**
 * Service permettant de calculer la prochaine occurrence d'une opération mensuelle
 */
class MonthlyTransactionOccurrenceCalculatorService
{
    /**
     * Calcule l'occurrence de l'opération mensuelle $monthlyTransaction sur le mois $month $year
     * @param MonthlyTransaction $monthlyTransaction
     * @param int $year
     * @param string $month
     * @return DateTime
     */
    public function calculateMonthlyTransactionOccurrence(
        MonthlyTransaction $monthlyTransaction,
        int $year,
        string $month
    ): DateTime {
        $dayWithLeadingZero = str_pad($monthlyTransaction->getDay(), 2, '0', STR_PAD_LEFT);

        return checkdate((int) $month, $monthlyTransaction->getDay(), $year) ?
            new DateTime("$year-$month-$dayWithLeadingZero") :
            $this->getLastDayOfMonth($year, $month)
        ;
    }

    /**
     * Calcule la prochaine occurrence de l'opération mensuelle $monthlyTransaction
     * @param MonthlyTransaction $monthlyTransaction
     * @return DateTime
     */
    public function calculateNextMonthlyTransactionOccurrence(MonthlyTransaction $monthlyTransaction): DateTime
    {
        $nextOccurrence = new DateTime();

        if ($monthlyTransaction->getDay() <= $nextOccurrence->format('j')) {
            $nextOccurrence->modify('next month');
        }

        return $this->calculateMonthlyTransactionOccurrence(
            $monthlyTransaction,
            $nextOccurrence->format('Y'),
            $nextOccurrence->format('m')
        );
    }

    /**
     * Retourne le dernier jour du mois $month $year
     * @param int $year
     * @param string $month
     * @return DateTime
     */
    private function getLastDayOfMonth(int $year, string $month): DateTime
    {
        return (new DateTime("$year-$month-01"))->modify('last day of this month');
    }
}
