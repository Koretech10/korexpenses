<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'root:manage',
    description: "Changer le mot de passe du compte root. Le créer s'il n'existe pas.",
)]
class RootManageCommand extends Command
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $em,
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'create-only',
                'c',
                InputOption::VALUE_NONE,
                "Créer le compte root uniquement. S'il existe déjà, le mot de passe ne sera pas modifié."
            )
            ->addArgument('password', InputArgument::OPTIONAL, "Nouveau mot de passe de root")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $root = $this->userRepository->findOneBy(['username' => 'root']);
        if ($root && $input->getOption('create-only')) {
            $output->writeln("<comment>Le compte root existe déjà et l'option --create-only est active.</comment>");
            $output->writeln("<comment>Le mot de passe root n'a pas été modifié.</comment>");

            return Command::SUCCESS;
        }

        $password = $input->getArgument('password');
        $isPasswordOk = preg_match("/(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#$%^&*])(?=.{16,})/", $password);

        if (!$password || !$isPasswordOk) {
            if (!$isPasswordOk) {
                $output->writeln('<error>Le mot de passe ne répond pas aux exigences de sécurité.</error>');
            }

            // Conditions à respecter pour le mot de passe
            $output->writeln([
                "Vous êtes sur le point d'initialiser ou de modifier le mot de passe du compte root de l'application.",
                "Ce mot de passe doit répondre aux exigences de sécurité suivantes :",
                "    - 16 caractères minimum",
                "    - Au moins une lettre minuscule",
                "    - Au moins une lettre MAJUSCULE",
                "    - Au moins un chiffre",
                "    - Au moins un de ces caractères spéciaux : ! @ # $ % ^ & *"
            ]);

            // Pose la question
            $helper = $this->getHelper('question');
            $passwordQuestion = new Question('<question>Entrez le nouveau mot de passe root :</question> ');

            // Configure la confidentialité, la validation et l'infinité de la question
            $passwordQuestion
                ->setHidden(true)
                ->setHiddenFallback(false)
                ->setValidator(function (?string $value): string {
                    if (preg_match("/(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#$%^&*])(?=.{16,})/", $value)) {
                        return $value;
                    }

                    throw new \Exception('Le mot de passe ne répond pas aux exigences de sécurité.');
                })
                ->setMaxAttempts(null)
            ;

            // Enregistrer la réponse
            $password = $helper->ask($input, $output, $passwordQuestion);
        }

        if (!$root) { // Créer l'utilisateur root car il n'existe pas
            $output->writeln("<comment>Le compte root n'existe pas. Il va être créé.</comment>");
            $root = new User();
            $root
                ->setUsername('root')
                ->setPassword($this->passwordHasher->hashPassword($root, $password))
                ->setRoles([
                    'ROLE_USER',
                    'ROLE_ADMIN'
                ])
            ;
            $this->em->persist($root);

            $output->writeln('<info>Compte root créé avec succès.</info>');
        } else { // Mettre à jour le mot de passe root
            $root->setPassword($this->passwordHasher->hashPassword($root, $password));

            $output->writeln('<info>Mot de passe root modifié avec succès.</info>');
        }

        $this->em->flush();

        return Command::SUCCESS;
    }
}
