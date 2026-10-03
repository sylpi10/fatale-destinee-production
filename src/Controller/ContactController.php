<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\ContactInput;
use App\Dto\Page\ContactPageProps;
use Nytodev\InertiaBundle\Service\Inertia;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;
use Symfony\Component\Routing\Attribute\Route;

final class ContactController extends AbstractController
{
    public function __construct(
        private readonly Inertia $inertia,
        #[Autowire("%env(CONTACT_EMAIL)%")] private readonly string $contactEmail,
    ) {}

    #[Route("/contact", name: "app_contact", methods: ["GET"])]
    public function show(): Response
    {
        return $this->inertia->render(
            "Contact",
            get_object_vars(
                new ContactPageProps(
                    name: "Teddy Moulin",
                    phone: "+33 6 86 55 40 86",
                    email: $this->contactEmail,
                    registrationPhone: "06 86 55 40 86",
                    registrationEmail: "ciedomovoi@gmail.com",
                ),
            ),
        );
    }

    #[Route("/contact", name: "app_contact_submit", methods: ["POST"])]
    public function submit(
        #[MapRequestPayload] ContactInput $input,
        Request $request,
        MailerInterface $mailer,
        #[Target("contact_form")] RateLimiterFactoryInterface $contactFormLimiter,
    ): Response {
        $redirect = $this->redirectToRoute("app_contact", status: Response::HTTP_SEE_OTHER);

        if (!$contactFormLimiter->create($request->getClientIp())->consume()->isAccepted()) {
            $this->inertia->flash("error", "Trop de messages envoyés. Merci de réessayer dans quelques minutes.");

            return $redirect;
        }

        // Pretend it worked so bots don't learn about the honeypot.
        if (!$input->isSpam()) {
            $mailer->send(
                new Email()
                    ->from(new Address($this->contactEmail, "Site Fatale Destinée"))
                    ->to($this->contactEmail)
                    ->replyTo(new Address($input->email, $input->name))
                    ->subject("[Contact] " . $input->subject)
                    ->text(sprintf("De : %s <%s>\n\n%s", $input->name, $input->email, $input->message)),
            );
        }

        $this->inertia->flash("success", "Merci, votre message a bien été envoyé !");

        return $redirect;
    }
}
