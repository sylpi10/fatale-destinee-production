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
        yield 'local' => ['/compagnie-clown-theatre-montpellier', 'Compagnie de clown et de théâtre à Montpellier'];
        yield 'legal' => ['/mentions-legales', 'Mentions légales'];
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

    public function testEveryPageHasItsOwnMetaDescription(): void
    {
        $client = static::createClient();
        $descriptions = [];
        foreach (['/', ...array_column(iterator_to_array(self::pages()), 0)] as $url) {
            $crawler = $client->request('GET', $url);
            $descriptions[$url] = $crawler->filter('meta[name="description"]')->attr('content');

            self::assertNotEmpty($descriptions[$url], $url);
            self::assertLessThanOrEqual(160, mb_strlen($descriptions[$url]), $url);
        }

        self::assertSame($descriptions, array_unique($descriptions));
    }

    public function testTheLocalPageIsLinkedFromTheFooterAndDescribesTheCompany(): void
    {
        $client = static::createClient();
        $client->request('GET', '/contact');
        $crawler = $client->clickLink('Compagnie de clown à Montpellier');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('title', 'Compagnie de clown et de théâtre à Montpellier');
        $data = json_decode($crawler->filter('script[type="application/ld+json"]')->text(), true, flags: \JSON_THROW_ON_ERROR);
        self::assertSame('PerformingGroup', $data['@type']);
        self::assertSame('Fatale Destinée Production', $data['name']);
        self::assertSame('Montpellier', $data['areaServed'][0]['name']);
        self::assertSame('http://localhost/', $data['url']);
    }

    public function testTheLegalNoticeIsLinkedFromTheFooterAndNamesThePublisherAndTheHost(): void
    {
        $client = static::createClient();
        $client->request('GET', '/agenda');
        $client->clickLink('Mentions légales');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('main', 'Fatale Destinée Production');
        self::assertSelectorTextContains('main', 'o2switch');
        self::assertSelectorExists('main a[href^="mailto:"]');
        self::assertSelectorExists('main a[href="/contact"]');
        // Nothing filled in the admin yet: no empty line, no placeholder.
        self::assertSelectorTextNotContains('main', 'Siège social');
        self::assertSelectorTextNotContains('main', 'compléter');
    }

    public function testEveryImageHasAnAltAttribute(): void
    {
        $client = static::createClient();
        foreach (['/', ...array_column(iterator_to_array(self::pages()), 0)] as $url) {
            $crawler = $client->request('GET', $url);

            // An empty alt is allowed (decorative image), a missing one is not.
            self::assertSame(0, $crawler->filter('img:not([alt])')->count(), $url);
            // Without its size, an image makes the page jump when it loads.
            self::assertSame(0, $crawler->filter('img:not([width]), img:not([height])')->count(), $url);
        }
    }

    public function testKeyboardUsersCanSkipToTheContentAndOpenTheMenu(): void
    {
        $client = static::createClient();
        foreach (['/', '/agenda'] as $url) {
            $crawler = $client->request('GET', $url);

            // The skip link is the first link of the page and leads to <main>.
            self::assertSame('#contenu', $crawler->filter('a')->first()->attr('href'), $url);
            self::assertSame('main', $crawler->filter('#contenu')->nodeName(), $url);
        }

        self::assertSelectorExists('button[aria-controls="site-menu"][aria-expanded="false"]');
        self::assertSelectorExists('nav#site-menu[aria-label="Menu principal"][hidden]');
        // What the menu makes inert while it is open.
        self::assertSelectorExists('main[data-menu-inert]');
        self::assertSelectorExists('footer[data-menu-inert]');
    }

    public function testEveryPageHasASharePreviewAndACanonicalUrl(): void
    {
        $client = static::createClient();
        foreach (['/', ...array_column(iterator_to_array(self::pages()), 0)] as $url) {
            $crawler = $client->request('GET', $url);
            $meta = static fn (string $property): ?string => $crawler->filter(\sprintf('meta[property="%s"]', $property))->attr('content');

            self::assertSame('http://localhost'.$url, $crawler->filter('link[rel="canonical"]')->attr('href'), $url);
            self::assertSame('http://localhost'.$url, $meta('og:url'), $url);
            self::assertSame($crawler->filter('title')->text(), $meta('og:title'), $url);
            self::assertSame($crawler->filter('meta[name="description"]')->attr('content'), $meta('og:description'), $url);
            self::assertMatchesRegularExpression('#^http://localhost/assets/images/og-fatale-destinee-[\w-]+\.jpg$#', (string) $meta('og:image'), $url);
        }
    }

    public function testFontsAreServedByTheSiteItself(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/agenda');

        // No request to Google Fonts: faster, and no visitor IP sent to Google.
        self::assertStringNotContainsString('fonts.googleapis.com', (string) $client->getResponse()->getContent());
        // Compared without the version AssetMapper adds to the name: "-" and 7 characters.
        self::assertSame(
            ['/assets/fonts/lora-latin-400-normal', '/assets/fonts/oswald-latin-700-normal'],
            $crawler->filter('link[rel="preload"][as="font"][crossorigin]')->each(
                static fn ($link): string => preg_replace('/-.{7}\.woff2$/', '', (string) $link->attr('href')),
            ),
        );
    }

    public function testLoginPageIsNotIndexed(): void
    {
        $client = static::createClient();
        $client->request('GET', '/login');

        self::assertSelectorExists('meta[name="robots"][content="noindex"]');
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

    public function testUnknownPageRendersTheSiteErrorPage(): void
    {
        // The custom error templates replace Symfony's exception page only without debug.
        $client = static::createClient(['debug' => false]);
        $client->request('GET', '/nope');

        self::assertResponseStatusCodeSame(404);
        self::assertSelectorTextContains('.page-title p', 'Page introuvable');
    }
}
