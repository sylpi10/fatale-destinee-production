<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\ContactInput;
use App\Form\ContactType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;
use Symfony\Component\Routing\Attribute\Route;

final class ContactController extends AbstractController
{
    public function __construct(
        #[Autowire("%env(CONTACT_EMAIL)%")] private readonly string $contactEmail,
    ) {}

    #[Route("/contact", name: "app_contact", methods: ["GET", "POST"])]
    public function index(
        Request $request,
        MailerInterface $mailer,
        #[Target("contact_form")] RateLimiterFactoryInterface $contactFormLimiter,
    ): Response {
        $input = new ContactInput();
        $form = $this->createForm(ContactType::class, $input);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$contactFormLimiter->create($request->getClientIp())->consume()->isAccepted()) {
                $this->addFlash("error", "Trop de messages envoyés. Merci de réessayer dans quelques minutes.");

                return $this->redirectToRoute("app_contact", status: Response::HTTP_SEE_OTHER);
            }

            // Pretend it worked so bots don't learn about the honeypot.
            if (!$input->isSpam()) {
                $mailer->send(
                    new Email()
                        ->from(new Address($this->contactEmail, "Site Fatale Destinée"))
                        ->to($this->contactEmail)
                        ->replyTo(new Address((string) $input->email, (string) $input->name))
                        ->subject("[Contact] " . $input->subject)
                        ->text(sprintf("De : %s <%s>\n\n%s", $input->name, $input->email, $input->message)),
                );
            }

            $this->addFlash("success", "Merci, votre message a bien été envoyé !");

            return $this->redirectToRoute("app_contact", status: Response::HTTP_SEE_OTHER);
        }

        return $this->render("contact/index.html.twig", [
            "form" => $form,
            "contact_email" => $this->contactEmail,
        ]);
    }
}
