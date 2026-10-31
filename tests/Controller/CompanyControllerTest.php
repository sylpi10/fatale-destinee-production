<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Show;
use App\Entity\User;
use App\Repository\CompanyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class CompanyControllerTest extends WebTestCase
{
    public function testCompanyPageShowsThePresentationAndTheMembersInOrder(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $entityManager = $container->get(EntityManagerInterface::class);

        $container->get(CompanyRepository::class)->get()->setPresentation("Premier paragraphe.\n\nSecond paragraphe.");
        $angeline = (new User())->setUsername('angeline')->setFirstName('Angéline')->setLastName('Delourme')
            ->setMember(true)->setPosition(2)->setBio('Bio d’Angéline.');
        $teddy = (new User())->setUsername('teddy')->setFirstName('Teddy')->setLastName('Moulin')
            ->setMember(true)->setPosition(1)->setBio("Bio de Teddy.\n\nSuite.");
        $admin = (new User())->setUsername('admin')->setRoles(['ROLE_ADMIN']);
        $show = (new Show())->setTitle('Qu\'est-ce que je fous là ?')->setSlug('qu-est-ce-que-je-fous-la')->setPublished(true);
        $draft = (new Show())->setTitle('Brouillon')->setSlug('brouillon');
        $teddy->addShow($show)->addShow($draft);
        foreach ([$angeline, $teddy, $admin, $show, $draft] as $entity) {
            $entityManager->persist($entity);
        }
        $entityManager->flush();
        $entityManager->clear();

        $crawler = $client->request('GET', '/compagnie');

        self::assertResponseIsSuccessful();
        self::assertSame(
            ['Premier paragraphe.', 'Second paragraphe.'],
            $crawler->filter('.company-intro p')->each(static fn ($p): string => $p->text()),
        );
        // Members only (not the admin account), in their order.
        self::assertSame(
            ['Teddy Moulin', 'Angéline Delourme'],
            $crawler->filter('.member h2')->each(static fn ($h2): string => $h2->text()),
        );
        self::assertSelectorCount(2, '.member:first-child > p');
        // Only the published shows of the member, linked to their page.
        self::assertSelectorCount(1, '.member:first-child .member-shows a');
        self::assertSame('/spectacles/qu-est-ce-que-je-fous-la', $crawler->filter('.member-shows a')->attr('href'));
    }

    public function testCompanyPageWorksBeforeAnythingIsFilledIn(): void
    {
        $client = static::createClient();
        $client->request('GET', '/compagnie');

        self::assertResponseIsSuccessful();
        // The logo stands in for the image of the company.
        self::assertSelectorExists('.company-intro img[src*="logo-fdp"]');
        self::assertSelectorNotExists('.members');
    }
}
