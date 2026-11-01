<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Performance;
use App\Entity\Show;
use App\Entity\ShowCredit;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Clock\Test\ClockSensitiveTrait;

final class ShowControllerTest extends WebTestCase
{
    use ClockSensitiveTrait;

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
        // No document, date or photo: no "Espace pro", "Quelques dates jouées" or carousel.
        self::assertSelectorNotExists('.pro-links');
        self::assertSelectorNotExists('.played-dates');
        self::assertSelectorNotExists('.carousel');
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

    public function testPastDatesAreOnTheShowPageAndUpcomingOnesOnTheAgenda(): void
    {
        static::mockTime('2026-06-15 10:00');
        $show = (new Show())
            ->setTitle('Girouette')
            ->setSlug('girouette')
            ->setPublished(true)
            ->addPerformance((new Performance())->setDate(new \DateTimeImmutable('2026-03-02'))->setPlace('Perpignan'))
            ->addPerformance((new Performance())->setDate(new \DateTimeImmutable('2026-05-20'))->setPlace('Montpellier')->setEvent('Festival Saperlipopette'))
            ->addPerformance((new Performance())->setDate(new \DateTimeImmutable('2026-06-15'))->setPlace('Sète'))
            ->addPerformance((new Performance())->setDate(new \DateTimeImmutable('2026-09-01'))->setPlace('Nîmes'));
        $draft = (new Show())->setTitle('Brouillon 2')->setSlug('brouillon-2')
            ->addPerformance((new Performance())->setDate(new \DateTimeImmutable('2026-07-01'))->setPlace('Lyon'));
        $this->persist($show, $draft);

        $this->client->request('GET', '/spectacles/girouette');
        self::assertSame(
            ['20 mai 2026 – Montpellier – Festival Saperlipopette', '2 mars 2026 – Perpignan'],
            $this->client->getCrawler()->filter('.played-dates li')->each(static fn ($li): string => $li->text()),
        );

        // Today's date is still to come; the draft show's date is not listed.
        $this->client->request('GET', '/agenda');
        self::assertSame(
            [['15 juin 2026', 'Girouette', 'Sète'], ['1 sept. 2026', 'Girouette', 'Nîmes']],
            $this->client->getCrawler()->filter('.agenda-row')->each(static fn ($row): array => [
                $row->filter('.agenda-row-date')->text(),
                $row->filter('.agenda-row-show')->text(),
                $row->filter('.agenda-row-place')->text(),
            ]),
        );
        $this->client->clickLink('Girouette');
        self::assertSelectorTextSame('h1', 'Girouette');
    }

    public function testThePhotosOfTheShowAreInACarousel(): void
    {
        $this->persist((new Show())
            ->setTitle('Girouette')
            ->setSlug('girouette')
            ->setPublished(true)
            ->setGalleryPhotos(['girouette-1.webp', 'girouette-2.webp']));

        $this->client->request('GET', '/spectacles/girouette');

        self::assertSame(
            ['/uploads/spectacles/girouette-1.webp', '/uploads/spectacles/girouette-2.webp'],
            $this->client->getCrawler()->filter('[data-controller="carousel"] img')->each(static fn ($img): ?string => $img->attr('src')),
        );
        self::assertSelectorExists('[data-action="carousel#next"]');
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
