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
        // The photo lists are NOT NULL columns (empty list by default): EasyAdmin would make them required.
        self::assertSelectorNotExists('input[name^="Show[galleryPhotos]"][required]');
        self::assertSelectorNotExists('input[name^="Show[pressPhotos]"][required]');
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

    public function testUploadedPhotoIsStoredAndShownAsWebp(): void
    {
        $client = static::createClient();
        $client->loginUser($this->createUser(['ROLE_ADMIN']));
        $client->followRedirects();

        $png = sys_get_temp_dir().'/affiche-'.bin2hex(random_bytes(4)).'.png';
        imagepng(imagecreatetruecolor(30, 20), $png);

        $crawler = $client->request('GET', '/admin');
        $crawler = $client->click($crawler->selectLink('Créer Spectacle')->link());
        $form = $crawler->selectButton('Créer')->form([
            'Show[title]' => 'Spectacle illustré',
            'Show[slug]' => 'spectacle-illustre',
            'Show[published]' => '1',
        ]);
        $form['Show[image][file]']->upload($png);
        $client->submit($form);
        self::assertResponseIsSuccessful();

        $show = static::getContainer()->get(ShowRepository::class)->findOneBy(['slug' => 'spectacle-illustre']);
        self::assertInstanceOf(Show::class, $show);
        self::assertStringEndsWith('.webp', (string) $show->getImage());
        $stored = static::getContainer()->getParameter('kernel.project_dir').'/public/uploads/spectacles/'.$show->getImage();

        try {
            self::assertSame(IMAGETYPE_WEBP, exif_imagetype($stored));

            $client->request('GET', '/spectacles/spectacle-illustre');
            self::assertSelectorExists(\sprintf('img[src$="/uploads/spectacles/%s"]', $show->getImage()));
        } finally {
            @unlink($stored);
            @unlink($png);
        }
    }

    public function testAdminUploadsATeaserVideoAndSeveralPressPhotos(): void
    {
        $client = static::createClient();
        $client->loginUser($this->createUser(['ROLE_ADMIN']));
        $client->followRedirects();

        $dir = sys_get_temp_dir().'/show-files-'.bin2hex(random_bytes(4));
        mkdir($dir);
        // The smallest file recognised as MP4: an "ftyp" box.
        file_put_contents($dir.'/teaser.mp4', "\x00\x00\x00\x18ftypmp42\x00\x00\x00\x00mp42isom");
        imagejpeg(imagecreatetruecolor(30, 20), $dir.'/photo-1.jpg');
        imagejpeg(imagecreatetruecolor(20, 30), $dir.'/photo-2.jpg');

        $crawler = $client->request('GET', '/admin');
        $crawler = $client->click($crawler->selectLink('Créer Spectacle')->link());
        $form = $crawler->selectButton('Créer')->form([
            'Show[title]' => 'Spectacle filmé',
            'Show[slug]' => 'spectacle-filme',
            'Show[published]' => '1',
        ]);
        $form['Show[teaserFile][file]']->upload($dir.'/teaser.mp4');
        $values = $form->getPhpFiles();
        $values['Show']['pressPhotos']['file'] = [
            ['name' => 'photo-1.jpg', 'type' => 'image/jpeg', 'tmp_name' => $dir.'/photo-1.jpg', 'error' => \UPLOAD_ERR_OK, 'size' => filesize($dir.'/photo-1.jpg')],
            ['name' => 'photo-2.jpg', 'type' => 'image/jpeg', 'tmp_name' => $dir.'/photo-2.jpg', 'error' => \UPLOAD_ERR_OK, 'size' => filesize($dir.'/photo-2.jpg')],
        ];
        $client->request('POST', $form->getUri(), $form->getPhpValues(), $values);
        self::assertResponseIsSuccessful();

        $show = static::getContainer()->get(ShowRepository::class)->findOneBy(['slug' => 'spectacle-filme']);
        self::assertInstanceOf(Show::class, $show);
        $uploadDir = static::getContainer()->getParameter('kernel.project_dir').'/public/uploads/spectacles/';
        $stored = [$show->getTeaserFile(), ...$show->getPressPhotos()];

        try {
            self::assertStringEndsWith('.mp4', (string) $show->getTeaserFile());
            self::assertCount(2, $show->getPressPhotos());
            foreach ($show->getPressPhotos() as $photo) {
                // Kept as JPEG for print, not converted to WebP.
                self::assertSame(IMAGETYPE_JPEG, exif_imagetype($uploadDir.$photo));
            }

            $client->request('GET', '/spectacles/spectacle-filme');
            self::assertSelectorExists(\sprintf('.teaser video[src$="/uploads/spectacles/%s"]', $show->getTeaserFile()));
            self::assertSelectorCount(2, '.pro-links a[download]');
        } finally {
            foreach (array_filter($stored) as $file) {
                @unlink($uploadDir.$file);
            }
            array_map('unlink', glob($dir.'/*'));
            rmdir($dir);
        }
    }

    public function testAdminAddsADateAndCarouselPhotosConvertedToWebp(): void
    {
        $client = static::createClient();
        $client->loginUser($this->createUser(['ROLE_ADMIN']));
        $client->followRedirects();

        $dir = sys_get_temp_dir().'/show-gallery-'.bin2hex(random_bytes(4));
        mkdir($dir);
        imagepng(imagecreatetruecolor(30, 20), $dir.'/scene-1.png');
        imagejpeg(imagecreatetruecolor(20, 30), $dir.'/scene-2.jpg');

        $crawler = $client->request('GET', '/admin');
        $crawler = $client->click($crawler->selectLink('Créer Spectacle')->link());
        $form = $crawler->selectButton('Créer')->form([
            'Show[title]' => 'Spectacle en tournée',
            'Show[slug]' => 'spectacle-en-tournee',
            'Show[published]' => '1',
        ]);
        // What the "Ajouter" button of the collection adds in the browser.
        $values = $form->getPhpValues();
        $values['Show']['performances'][0] = ['date' => '2025-04-12', 'place' => 'Perpignan', 'event' => 'Festival de printemps'];
        $files = $form->getPhpFiles();
        $files['Show']['galleryPhotos']['file'] = [
            ['name' => 'scene-1.png', 'type' => 'image/png', 'tmp_name' => $dir.'/scene-1.png', 'error' => \UPLOAD_ERR_OK, 'size' => filesize($dir.'/scene-1.png')],
            ['name' => 'scene-2.jpg', 'type' => 'image/jpeg', 'tmp_name' => $dir.'/scene-2.jpg', 'error' => \UPLOAD_ERR_OK, 'size' => filesize($dir.'/scene-2.jpg')],
        ];
        $client->request('POST', $form->getUri(), $values, $files);
        self::assertResponseIsSuccessful();

        $show = static::getContainer()->get(ShowRepository::class)->findOneBy(['slug' => 'spectacle-en-tournee']);
        self::assertInstanceOf(Show::class, $show);
        $uploadDir = static::getContainer()->getParameter('kernel.project_dir').'/public/uploads/spectacles/';

        try {
            self::assertCount(1, $show->getPerformances());
            self::assertSame('2025-04-12', $show->getPerformances()->first()->getDate()?->format('Y-m-d'));
            self::assertCount(2, $show->getGalleryPhotos());
            foreach ($show->getGalleryPhotos() as $photo) {
                self::assertSame(IMAGETYPE_WEBP, exif_imagetype($uploadDir.$photo));
            }

            $client->request('GET', '/spectacles/spectacle-en-tournee');
            self::assertSelectorTextContains('.played-dates', 'Perpignan – Festival de printemps');
            self::assertSelectorCount(2, '.carousel img');
        } finally {
            foreach ($show->getGalleryPhotos() as $photo) {
                @unlink($uploadDir.$photo);
            }
            array_map('unlink', glob($dir.'/*'));
            rmdir($dir);
        }
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
