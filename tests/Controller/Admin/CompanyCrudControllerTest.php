<?php

declare(strict_types=1);

namespace App\Tests\Controller\Admin;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class CompanyCrudControllerTest extends WebTestCase
{
    public function testAdminFillsInTheLegalNoticeShownOnTheSite(): void
    {
        $client = static::createClient();
        $client->loginUser($this->createAdmin());
        $client->followRedirects();

        $crawler = $client->request('GET', '/admin');
        $client->click($crawler->selectLink('La compagnie')->link());
        self::assertSelectorExists('a:contains("Voir les mentions légales")');
        $client->submitForm('Sauvegarder et modifier', [
            'Company[legalForm]' => 'association loi 1901',
            'Company[registrationNumber]' => '123 456 789 00012',
            'Company[address]' => "1 rue de l’Exemple\n34000 Montpellier",
            'Company[publicationDirector]' => 'Angéline Delourme',
        ]);
        self::assertResponseIsSuccessful();

        $client->request('GET', '/mentions-legales');
        self::assertSelectorTextContains('main', 'Fatale Destinée Production, association loi 1901, SIRET 123 456 789 00012.');
        self::assertSelectorTextContains('main', '34000 Montpellier');
        self::assertSelectorTextContains('main', 'Direction de la publication : Angéline Delourme.');
    }

    public function testAnAssociationWithoutSiretShowsItsRnaNumber(): void
    {
        $client = static::createClient();
        $client->loginUser($this->createAdmin());
        $client->followRedirects();

        $crawler = $client->request('GET', '/admin');
        $client->click($crawler->selectLink('La compagnie')->link());
        $client->submitForm('Sauvegarder et modifier', [
            'Company[registrationNumber]' => 'W343012345',
        ]);

        $client->request('GET', '/mentions-legales');
        self::assertSelectorTextContains('main', 'Fatale Destinée Production, RNA W343012345.');
    }

    private function createAdmin(): User
    {
        $user = (new User())->setUsername('admin-legal')->setRoles(['ROLE_ADMIN']);
        $user->setPassword('not-used');

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $entityManager->persist($user);
        $entityManager->flush();

        return $user;
    }
}
