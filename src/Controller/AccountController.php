<?php

namespace App\Controller;

use App\Entity\Account;
use App\Form\AccountType;
use App\Repository\AccountRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AccountController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly AccountRepository $accountRepository,
        private readonly PaginatorInterface $pager
    ) {
    }

    /**
     * Lister les comptes bancaires
     * @param int $page
     * @return Response
     */
    #[Route('/account/list/{page}', name: 'account_list', requirements: ["page" => "\d+"])]
    public function list(int $page = 1): Response
    {
        // Formulaire de création de compte bancaire
        $account = new Account();
        $newAccountForm = $this->createForm(AccountType::class, $account, [
            'target_url' => $this->generateUrl('account_create')
        ]);

        // Pagination des comptes bancaires
        $pagination = $this->pager->paginate(
            $this->accountRepository->getAccounts(),
            $page,
            100
        );

        return $this->render('account/list.html.twig', [
            'newAccountForm' => $newAccountForm->createView(),
            'pagination' => $pagination
        ]);
    }

    /**
     * Créer un nouveau compte bancaire
     * @param Request $request
     * @return RedirectResponse
     */
    #[Route('/account/create', name: 'account_create')]
    public function create(Request $request): RedirectResponse
    {
        // Récupération du formulaire de création
        $account = new Account();
        $newAccountForm = $this->createForm(AccountType::class, $account, [
            'target_url' => $this->generateUrl('account_create')
        ]);
        $newAccountForm->handleRequest($request);

        if ($newAccountForm->isSubmitted() && $newAccountForm->isValid()) {
            /** @var Account $account */
            $account = $newAccountForm->getData();
            $account->setBalance(0);

            $this->em->persist($account);
            $this->em->flush();
        } elseif (!$newAccountForm->isValid()) {
            foreach ($newAccountForm->getErrors(true) as $error) {
                $this->addFlash('danger', $error->getMessage());
            }
        }

        return $this->redirectToRoute('account_list');
    }

    /** Éditer le compte bancaire $account
     * @param Account $account
     * @param Request $request
     * @return RedirectResponse|Response
     */
    #[Route('/account/edit/{id}', name: 'account_edit', requirements: ["id" => "\d+"])]
    public function edit(Account $account, Request $request): RedirectResponse|Response
    {
        $accountForm = $this->createForm(AccountType::class, $account);
        $accountForm->handleRequest($request);

        if ($accountForm->isSubmitted() && $accountForm->isValid()) {
            $account = $accountForm->getData();

            $this->em->flush();

            return $this->redirectToRoute('account_list');
        }

        return $this->render('account/edit.html.twig', [
            'accountForm' => $accountForm->createView()
        ]);
    }

    #[Route('/account/delete/{id}', name: 'account_delete', requirements: ["id" => "\d+"])]
    public function delete(Account $account)
    {
    }
}
