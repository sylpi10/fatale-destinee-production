<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Creates an admin account, or resets its password if it exists: there is no sign-up page.
 */
#[AsCommand(name: 'app:admin:create', description: 'Create an admin account (or reset its password)')]
final class CreateAdminCommand
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly EntityManagerInterface $entityManager,
        private readonly ValidatorInterface $validator,
    ) {}

    public function __invoke(SymfonyStyle $io, #[Argument('Username of the admin')] string $username): int
    {
        $username = trim($username);
        $password = (string) $io->askHidden('Password (8 characters minimum)', static function (?string $value): string {
            if (mb_strlen((string) $value) < 8) {
                throw new \RuntimeException('The password must be at least 8 characters long.');
            }

            return (string) $value;
        });

        $user = $this->userRepository->findOneBy(['username' => $username]);
        $created = null === $user;
        $user ??= new User()->setUsername($username);

        $user->setRoles(['ROLE_ADMIN']);
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));

        $violations = $this->validator->validate($user);
        if (\count($violations) > 0) {
            foreach ($violations as $violation) {
                $io->error(sprintf('%s: %s', $violation->getPropertyPath(), $violation->getMessage()));
            }

            return Command::FAILURE;
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success(sprintf('Admin %s %s.', $username, $created ? 'created' : 'updated'));

        return Command::SUCCESS;
    }
}
