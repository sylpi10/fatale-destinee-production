<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\CompanyRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Legal notice required by French law (LCEN, art. 6) and the privacy notice (RGPD), linked from the footer.
 * The publisher's details are edited in the admin, with the company.
 */
final class LegalController extends AbstractController
{
    #[Route("/mentions-legales", name: "app_legal")]
    public function index(
        CompanyRepository $companyRepository,
        #[Autowire("%env(CONTACT_EMAIL)%")] string $contactEmail,
    ): Response
    {
        return $this->render("legal/index.html.twig", [
            "company" => $companyRepository->get(),
            "contact_email" => $contactEmail,
        ]);
    }
}
