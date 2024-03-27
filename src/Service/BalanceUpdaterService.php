<?php

namespace App\Service;

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
}