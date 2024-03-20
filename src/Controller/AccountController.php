<?php

namespace App\Controller;

use App\Entity\Account;
use App\Repository\AccountRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class AccountController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly AccountRepository $accountRepository,
        private readonly PaginatorInterface $pager
    ) {
    }

    #[Route('/account/list', name: 'account_list')]
    public function list()
    {
    }

    #[Route('/account/create', name: 'account_create')]
    public function create(Request $request)
    {
    }

    #[Route('/account/edit/{id}', name: 'account_edit', requirements: ["id" => "\d+"])]
    public function edit(Account $account)
    {
    }

    #[Route('/account/delete/{id}', name: 'account_delete', requirements: ["id" => "\d+"])]
    public function delete(Account $account)
    {
    }
}
