<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserRepository $userRepository,
        private readonly PaginatorInterface $pager
    ) {
    }

    /**
     * Lister les utilisateurs
     * @param int $page
     * @return Response
     */
    #[Route('/user/list/{page}', name: 'user_list', requirements: ['page' => '\d+'])]
    #[IsGranted('ROLE_ADMIN')]
    public function list(int $page = 1): Response
    {
        // Pagination des utilisateurs
        $pagination = $this->pager->paginate(
            $this->userRepository->getUsers(),
            $page,
            100
        );

        return $this->render('user/list.html.twig', [
            'pagination' => $pagination
        ]);
    }

    #[Route('/user/edit/admin/{id}', name: 'user_edit_admin', requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_ADMIN')]
    public function editAsAdmin(Request $request, User $user = null)
    {
    }
}
