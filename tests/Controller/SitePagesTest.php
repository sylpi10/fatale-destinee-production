<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Nytodev\InertiaBundle\Testing\AssertableInertiaPage;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class SitePagesTest extends WebTestCase
{
    /**
     * @return iterable<string, array{string, string}>
     */
    public static function pages(): iterable
    {
        yield 'home' => ['/', 'Home'];
        yield 'workshops' => ['/ateliers', 'Workshops'];
        yield 'speaker' => ['/intervenant', 'Speaker'];
        yield 'photos' => ['/photos', 'Photos'];
        yield 'contact' => ['/contact', 'Contact'];
    }

    #[DataProvider('pages')]
    public function testPageRendersItsComponent(string $url, string $component): void
    {
        $client = static::createClient();
        $client->request('GET', $url);

        self::assertResponseIsSuccessful();
        AssertableInertiaPage::fromResponse($client->getResponse())
            ->component($component)
            ->url($url);
    }

    public function testWorkshopsListsBothSlots(): void
    {
        $client = static::createClient();
        $client->request('GET', '/ateliers');

        AssertableInertiaPage::fromResponse($client->getResponse())
            ->count('slots', 2)
            ->where('slots.0.day', 'Mardi')
            ->where('price', "400 € l'année");
    }

    public function testPhotosListsTheGalleryFiles(): void
    {
        $client = static::createClient();
        $client->request('GET', '/photos');

        $expected = \count(glob(\dirname(__DIR__, 2).'/public/images/photos/*.jpg') ?: []);
        self::assertGreaterThan(0, $expected);

        AssertableInertiaPage::fromResponse($client->getResponse())
            ->count('photos', $expected)
            ->where('photos.0', '/images/photos/01.jpg');
    }
}
