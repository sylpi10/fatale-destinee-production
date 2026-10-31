<?php

declare(strict_types=1);

namespace App\Tests\Controller\Admin;

use App\Entity\Show;
use App\Entity\User;
use App\Repository\ShowRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ShowCrudControllerTest extends WebTestCase
{
    public function testAnonymousVisitorIsSentToTheLoginPage(): void
    {
        $client = static::createClient();
        $client->request('GET', '/admin');

        self::assertResponseRedirects('/login');
    }

    public function testUserWithoutAdminRoleIsDenied(): void
    {
        $client = static::createClient();
        $client->loginUser($this->createUser([]));
        $client->request('GET', '/admin');

        self::assertResponseStatusCodeSame(403);
    }

    public function testAdminCreatesAShowThatAppearsOnTheSite(): void
    {
        $client = static::createClient();
        $client->loginUser($this->createUser(['ROLE_ADMIN']));
        $client->followRedirects();

        $client->request('GET', '/admin');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Spectacles');

        $client->clickLink('Créer Spectacle');
        $client->submitForm('Créer', [
            'Show[title]' => 'Nouveau spectacle',
            'Show[slug]' => 'nouveau-spectacle',
            'Show[published]' => '1',
            'Show[genre]' => 'Clown',
            'Show[durationMinutes]' => '45',
            'Show[synopsis]' => 'Un synopsis.',
        ]);
        self::assertResponseIsSuccessful();

        $show = static::getContainer()->get(ShowRepository::class)->findOneBy(['slug' => 'nouveau-spectacle']);
        self::assertInstanceOf(Show::class, $show);
        self::assertSame(45, $show->getDurationMinutes());

        $client->request('GET', '/spectacles/nouveau-spectacle');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextSame('h1', 'Nouveau spectacle');
    }

    public function testAdminEditsTheCompanyAndAMember(): void
    {
        $client = static::createClient();
        $client->loginUser($this->createUser(['ROLE_ADMIN']));
        $client->followRedirects();

        $client->request('GET', '/admin');
        $client->clickLink('La compagnie');
        $client->submitForm('Sauvegarder et modifier', [
            'Company[presentation]' => 'Présentation modifiée.',
        ]);
        self::assertResponseIsSuccessful();

        $client->clickLink('Membres et comptes');
        $client->clickLink('Créer Membre');
        $client->submitForm('Créer', [
            'User[firstName]' => 'Teddy',
            'User[lastName]' => 'Moulin',
            'User[member]' => '1',
            'User[bio]' => 'Une bio.',
            'User[username]' => 'teddy',
        ]);
        self::assertResponseIsSuccessful();

        $client->request('GET', '/compagnie');
        self::assertSelectorTextSame('.company-intro p', 'Présentation modifiée.');
        self::assertSelectorTextSame('.member h2', 'Teddy Moulin');
    }

    /**
     * @param list<string> $roles
     */
    private function createUser(array $roles): User
    {
        $user = (new User())->setUsername('someone')->setRoles($roles);
        $user->setPassword('not-used');

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $entityManager->persist($user);
        $entityManager->flush();

        return $user;
    }
}
