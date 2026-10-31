<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Mime\Email;

final class ContactControllerTest extends WebTestCase
{
    private const array VALID_DATA = [
        'contact[name]' => 'Jeanne Dupont',
        'contact[email]' => 'jeanne@example.com',
        'contact[subject]' => 'Inscription',
        'contact[message]' => 'Bonjour, je souhaite m\'inscrire au groupe du mardi.',
        'contact[website]' => '',
    ];

    public function testValidMessageIsEmailedToTheCompany(): void
    {
        $client = static::createClient();
        $this->submit($client, self::VALID_DATA);

        self::assertResponseRedirects('/contact', 303);
        self::assertEmailCount(1);

        $email = self::getMailerMessage();
        self::assertInstanceOf(Email::class, $email);
        self::assertEmailAddressContains($email, 'To', 'contact@fataledestineeproduction.com');
        self::assertEmailHeaderSame($email, 'Subject', '[Contact] Inscription');
        self::assertSame('jeanne@example.com', $email->getReplyTo()[0]->getAddress());
        self::assertEmailTextBodyContains($email, 'groupe du mardi');

        $client->followRedirect();
        self::assertSelectorTextSame('.flash-success', 'Merci, votre message a bien été envoyé !');
    }

    public function testInvalidMessageIsRenderedBackWithErrors(): void
    {
        $client = static::createClient();
        $this->submit($client, ['contact[email]' => 'not-an-email'] + self::VALID_DATA);

        // 422 so Turbo renders the form again instead of ignoring the response.
        self::assertResponseStatusCodeSame(422);
        self::assertEmailCount(0);
        self::assertSelectorTextSame('.form-error', 'Cette adresse e-mail n\'est pas valide.');
        self::assertInputValueSame('contact[name]', 'Jeanne Dupont');
    }

    public function testHoneypotSilentlyDropsTheMessage(): void
    {
        $client = static::createClient();
        $this->submit($client, ['contact[website]' => 'https://spam.example'] + self::VALID_DATA);

        self::assertResponseRedirects('/contact', 303);
        self::assertEmailCount(0);
    }

    /**
     * @param array<string, string> $data
     */
    private function submit(KernelBrowser $client, array $data): void
    {
        $client->request('GET', '/contact');
        $client->submitForm('Envoyer', $data);
    }
}
