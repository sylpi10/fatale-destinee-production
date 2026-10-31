<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class SitePagesTest extends WebTestCase
{
    /**
     * @return iterable<string, array{string, string}>
     */
    public static function pages(): iterable
    {
        yield 'company' => ['/compagnie', 'La compagnie'];
        yield 'shows' => ['/spectacles', 'Spectacles'];
        yield 'agenda' => ['/agenda', 'Agenda'];
        yield 'workshops' => ['/ateliers', 'Les ateliers'];
        yield 'contact' => ['/contact', 'Contact'];
        yield 'login' => ['/login', 'Connexion'];
    }

    #[DataProvider('pages')]
    public function testInnerPageRendersInsideTheSiteLayout(string $url, string $title): void
    {
        $client = static::createClient();
        $client->request('GET', $url);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextSame('h1', $title);
        self::assertSelectorExists('.site-logo img');
        self::assertSelectorExists('button[aria-controls="site-menu"]');
        self::assertSelectorCount(6, '#site-menu a');
        self::assertSelectorExists('.felt-border');
        self::assertSelectorExists('.site-footer');
    }

    public function testHomeShowsTheLogoAndTheMenuOfTheBrief(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('h1 button[data-controller="logo-sound"] img[alt="Fatale Destinée Production"]');
        self::assertSame(
            ['Compagnie', 'Spectacles', 'Agenda', 'Ateliers'],
            $crawler->filter('.home-nav a')->each(static fn ($link): string => $link->text()),
        );
        self::assertSame('/compagnie', $crawler->filter('.home-nav a')->first()->attr('href'));
        self::assertSelectorCount(2, '.home-figure');
        self::assertSelectorExists('.felt-border');
        // The home page has its own layout: no MENU button or footer.
        self::assertSelectorNotExists('#site-menu');
        self::assertSelectorNotExists('.site-footer');
    }

    public function testMenuMarksTheCurrentSection(): void
    {
        $client = static::createClient();
        $client->request('GET', '/agenda');

        self::assertSelectorCount(1, '#site-menu a[aria-current="page"]');
        self::assertSelectorTextSame('#site-menu a[aria-current="page"]', 'Agenda');
    }

    public function testCompanyPresentsBothFounders(): void
    {
        $client = static::createClient();
        $client->request('GET', '/compagnie');

        self::assertSelectorCount(2, '.member');
        self::assertSelectorTextSame('.member:first-child h2', 'Teddy Moulin');
        self::assertSelectorTextSame('.member:last-child h2', 'Angéline Delourme');
    }

    public function testUnknownPageRendersTheSiteErrorPage(): void
    {
        // The custom error templates replace Symfony's exception page only without debug.
        $client = static::createClient(['debug' => false]);
        $client->request('GET', '/nope');

        self::assertResponseStatusCodeSame(404);
        self::assertSelectorTextContains('.page-title p', 'Page introuvable');
    }
}
