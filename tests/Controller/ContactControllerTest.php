<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Nytodev\InertiaBundle\Testing\AssertableInertiaPage;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Mime\Email;

final class ContactControllerTest extends WebTestCase
{
    private const array VALID_PAYLOAD = [
        'name' => 'Jeanne Dupont',
        'email' => 'jeanne@example.com',
        'subject' => 'Inscription',
        'message' => 'Bonjour, je souhaite m\'inscrire au groupe du mardi.',
        'website' => '',
    ];

    public function testValidMessageIsEmailedToTheCompany(): void
    {
        $client = static::createClient();
        $this->post($client, self::VALID_PAYLOAD);

        self::assertResponseRedirects('/contact', 303);
        self::assertEmailCount(1);

        $email = self::getMailerMessage();
        self::assertInstanceOf(Email::class, $email);
        self::assertEmailAddressContains($email, 'To', 'contact@fataledestineeproduction.com');
        self::assertEmailHeaderSame($email, 'Subject', '[Contact] Inscription');
        self::assertSame('jeanne@example.com', $email->getReplyTo()[0]->getAddress());
        self::assertEmailTextBodyContains($email, 'groupe du mardi');

        $client->request('GET', '/contact', server: ['HTTP_X_INERTIA' => 'true']);
        // Flash data sits at the page level, next to props: AssertableInertiaPage only reads props.
        $page = json_decode((string) $client->getResponse()->getContent(), true, flags: \JSON_THROW_ON_ERROR);
        self::assertSame('Merci, votre message a bien été envoyé !', $page['flash']['success'] ?? null);
    }

    public function testInvalidMessageRedirectsBackWithErrors(): void
    {
        $client = static::createClient();
        $this->post($client, ['email' => 'not-an-email'] + self::VALID_PAYLOAD);

        self::assertResponseRedirects('/contact', 303);
        self::assertEmailCount(0);

        $client->request('GET', '/contact', server: ['HTTP_X_INERTIA' => 'true']);
        AssertableInertiaPage::fromResponse($client->getResponse())
            ->where('errors.email', 'Cette adresse e-mail n\'est pas valide.');
    }

    public function testHoneypotSilentlyDropsTheMessage(): void
    {
        $client = static::createClient();
        $this->post($client, ['website' => 'https://spam.example'] + self::VALID_PAYLOAD);

        self::assertResponseRedirects('/contact', 303);
        self::assertEmailCount(0);
    }

    /**
     * @param array<string, string> $payload
     */
    private function post(KernelBrowser $client, array $payload): void
    {
        // Same flow as the browser: the GET sets the XSRF cookie, the POST echoes it in a header.
        $client->request('GET', '/contact');
        $token = $client->getCookieJar()->get('XSRF-TOKEN')?->getValue();

        $client->request(
            'POST',
            '/contact',
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X_INERTIA' => 'true',
                'HTTP_X_XSRF_TOKEN' => (string) $token,
                'HTTP_REFERER' => 'http://localhost/contact',
            ],
            content: json_encode($payload, \JSON_THROW_ON_ERROR),
        );
    }
}
