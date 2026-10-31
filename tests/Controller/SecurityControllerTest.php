<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class SecurityControllerTest extends WebTestCase
{
    private const string EMAIL = 'admin@example.com';

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $container = static::getContainer();
        $entityManager = $container->get(EntityManagerInterface::class);

        $user = $container->get(UserRepository::class)->findOneBy(['email' => self::EMAIL]) ?? new User();
        $user->setEmail(self::EMAIL);
        $user->setPassword($container->get(UserPasswordHasherInterface::class)->hashPassword($user, 'secret1234'));
        $entityManager->persist($user);
        $entityManager->flush();
    }

    public function testValidCredentialsLogTheUserIn(): void
    {
        $this->login('secret1234');

        self::assertResponseRedirects('/');
        $this->client->followRedirect();
        self::assertSame(self::EMAIL, $this->client->getContainer()->get('security.token_storage')->getToken()?->getUserIdentifier());
    }

    public function testWrongPasswordShowsAnError(): void
    {
        $this->login('wrong');

        self::assertResponseRedirects('/login');
        $this->client->followRedirect();
        self::assertSelectorTextSame('.form-error', 'Invalid credentials.');
        self::assertInputValueSame('email', self::EMAIL);
    }

    private function login(string $password): void
    {
        $this->client->request('GET', '/login');
        $this->client->submitForm('Se connecter', [
            'email' => self::EMAIL,
            'password' => $password,
        ]);
    }
}
