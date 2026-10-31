<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Show;
use App\Entity\ShowCredit;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ShowControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        $teddy = (new User())->setUsername('teddy')->setFirstName('Teddy')->setLastName('Moulin')->setMember(true);

        $this->persist(
            $teddy,
            (new Show())
                ->setTitle('Qu\'est-ce que je fous là ?')
                ->setSlug('qu-est-ce-que-je-fous-la')
                ->setPublished(true)
                ->setPosition(1)
                ->setMember($teddy)
                ->setGenre('Clown')
                ->setCreationYear(2026)
                ->setMinimumAge(8)
                ->setVenues('EXT / INT')
                ->setDurationMinutes(55)
                ->setCatchphrase('Moroze cherche sa place.')
                ->setSynopsis("Premier paragraphe.\n\nSecond paragraphe.")
                ->addCredit((new ShowCredit())->setRole('De et avec')->setPeople('Teddy Moulin')),
            (new Show())->setTitle('Brouillon')->setSlug('brouillon')->setPosition(0),
        );
    }

    public function testListShowsOnlyPublishedShows(): void
    {
        $this->client->request('GET', '/spectacles');

        self::assertResponseIsSuccessful();
        self::assertSelectorCount(1, '.show-card');
        self::assertSelectorTextSame('.show-card h2', 'Qu\'est-ce que je fous là ?');
        self::assertSelectorTextSame('.show-card p', 'Teddy Moulin · Clown · Création 2026');
    }

    public function testShowPageDisplaysTheShowFromTheDatabase(): void
    {
        $this->client->request('GET', '/spectacles');
        $this->client->clickLink('Qu\'est-ce que je fous là ?');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextSame('h1', 'Qu\'est-ce que je fous là ?');
        self::assertSelectorTextSame('.show-genre', 'Clown');
        self::assertSame(
            ['Création 2026', 'À partir de 8 ans', 'EXT / INT', '55 min'],
            $this->client->getCrawler()->filter('.show-facts li')->each(static fn ($li): string => $li->text()),
        );
        self::assertSelectorTextSame('.catchphrase', 'Moroze cherche sa place.');
        self::assertSelectorCount(2, '.show-columns p:not(.catchphrase):not(.placeholder)');
        self::assertSelectorTextSame('.credits dd', 'Teddy Moulin');
        // No document uploaded: no "Espace pro".
        self::assertSelectorNotExists('.pro-links');
        self::assertSelectorTextSame('#site-menu a[aria-current="page"]', 'Spectacles');
    }

    public function testAnUploadedTeaserWinsOverTheLinkAndPressPhotosAreDownloadable(): void
    {
        $this->persist((new Show())
            ->setTitle('Girouette')
            ->setSlug('girouette')
            ->setPublished(true)
            ->setTeaserUrl('https://player.vimeo.com/video/1')
            ->setTeaserFile('girouette-teaser.mp4')
            ->setPressPhotos(['girouette-1.jpg', 'girouette-2.jpg']));

        $this->client->request('GET', '/spectacles/girouette');

        self::assertSelectorExists('.teaser video[src="/uploads/spectacles/girouette-teaser.mp4"]');
        self::assertSelectorNotExists('.teaser iframe');
        self::assertSame(
            [['Photo communication 1', '/uploads/spectacles/girouette-1.jpg'], ['Photo communication 2', '/uploads/spectacles/girouette-2.jpg']],
            $this->client->getCrawler()->filter('.pro-links a[download]')->each(static fn ($a): array => [$a->text(), $a->attr('href')]),
        );
    }

    public function testATeaserLinkIsEmbedded(): void
    {
        $this->persist((new Show())
            ->setTitle('Girouette')
            ->setSlug('girouette')
            ->setPublished(true)
            ->setTeaserUrl('https://player.vimeo.com/video/1'));

        $this->client->request('GET', '/spectacles/girouette');

        self::assertSelectorExists('.teaser iframe[src="https://player.vimeo.com/video/1"]');
        self::assertSelectorNotExists('.teaser video');
    }

    public function testUnpublishedOrUnknownShowIsNotFound(): void
    {
        $this->client->request('GET', '/spectacles/brouillon');
        self::assertResponseStatusCodeSame(404);

        $this->client->request('GET', '/spectacles/nope');
        self::assertResponseStatusCodeSame(404);
    }

    private function persist(object ...$entities): void
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        foreach ($entities as $entity) {
            $entityManager->persist($entity);
        }
        $entityManager->flush();
    }
}
