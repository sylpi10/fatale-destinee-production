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
        yield 'home' => ['/', 'Fatale Destinée'];
        yield 'workshops' => ['/ateliers', 'Les ateliers'];
        yield 'speaker' => ['/intervenant', 'Intervenant'];
        yield 'photos' => ['/photos', 'Photos'];
        yield 'contact' => ['/contact', 'Contact'];
        yield 'login' => ['/login', 'Connexion'];
    }

    #[DataProvider('pages')]
    public function testPageRendersInsideTheSiteLayout(string $url, string $title): void
    {
        $client = static::createClient();
        $client->request('GET', $url);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextSame('.page-header h1', $title);
        self::assertSelectorCount(5, '.site-nav a');
        self::assertSelectorExists('.site-footer');
    }

    public function testMenuMarksTheCurrentPage(): void
    {
        $client = static::createClient();
        $client->request('GET', '/ateliers');

        self::assertSelectorCount(1, '.site-nav a[aria-current="page"]');
        self::assertSelectorTextSame('.site-nav a[aria-current="page"]', 'Les ateliers');
    }

    public function testWorkshopsListsBothSlots(): void
    {
        $client = static::createClient();
        $client->request('GET', '/ateliers');

        self::assertSelectorCount(2, '.slot');
        self::assertSelectorTextSame('.slot:first-child .slot-day', 'Mardi');
        self::assertSelectorTextSame('.price', "400 € l'année");
    }

    public function testPhotosListsTheGalleryFiles(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/photos');

        $expected = \count(glob(\dirname(__DIR__, 2).'/public/images/photos/*.jpg') ?: []);
        self::assertGreaterThan(0, $expected);

        self::assertSelectorCount($expected, '.gallery img[data-lightbox-target="thumb"]');
        self::assertSame('/images/photos/01.jpg', $crawler->filter('.gallery img')->first()->attr('src'));
    }

    public function testUnknownPageRendersTheSiteErrorPage(): void
    {
        // The custom error templates replace Symfony's exception page only without debug.
        $client = static::createClient(['debug' => false]);
        $client->request('GET', '/nope');

        self::assertResponseStatusCodeSame(404);
        self::assertSelectorTextContains('.ornament-text', 'Page introuvable');
    }
}
