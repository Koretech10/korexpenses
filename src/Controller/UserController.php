<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserRepository $userRepository,
        private readonly PaginatorInterface $pager,
        private readonly UserPasswordHasherInterface $passwordHasher
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

    /**
     * Créer un nouvel utilisateur
     * @param Request $request
     * @return RedirectResponse|Response
     */
    #[Route('/user/create', name: 'user_create')]
    #[IsGranted('ROLE_ADMIN')]
    public function create(Request $request): RedirectResponse|Response
    {
        // Formulaire de création
        $user = new User();
        $newUserForm = $this->createForm(UserType::class, $user);
        $newUserForm->handleRequest($request);

        if ($newUserForm->isSubmitted() && $newUserForm->isValid()) {
            /** @var User $user */
            $user = $newUserForm->getData();
            $user->setPassword($this->passwordHasher->hashPassword($user, $user->getPassword()));

            $this->em->persist($user);
            $this->em->flush();

            $this->addFlash('success', "L'utilisateur a été créé avec succès.");
            return $this->redirectToRoute('user_list');
        }

        return $this->render('user/create.html.twig', [
            'newUserForm' => $newUserForm->createView(),
        ]);
    }

    /**
     * Éditer l'utilisateur $user
     * @param Request $request
     * @param User|null $user
     * @return RedirectResponse|Response
     */
    #[Route('/user/edit/admin/{id}', name: 'user_edit_admin', requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_ADMIN')]
    public function editAsAdmin(Request $request, User $user = null): RedirectResponse|Response
    {
        // Vérification de l'existence de l'utilisateur demandé
        if (!$user) {
            $this->addFlash('danger', "Cet utilisateur n'existe pas.");
            return $this->redirectToRoute('user_list');
        }

        $userForm = $this->createForm(UserType::class, $user);
        $userForm->handleRequest($request);

        if ($userForm->isSubmitted() && $userForm->isValid()) {
            $user = $userForm->getData();

            $this->em->flush();
            $this->addFlash('success', 'Utilisateur modifié avec succès.');

            return $this->redirectToRoute('user_list');
        }

        return $this->render('user/edit_as_admin.html.twig', [
            'userForm' => $userForm->createView(),
            'user' => $user
        ]);
    }

    #[Route('/user/delete/{id}', name: 'user_delete', requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(User $user = null)
    {
    }
}
