<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CompanyController extends AbstractController
{
    // Static content for now, from the client brief: it moves to the database with the admin.
    #[Route("/compagnie", name: "app_company")]
    public function index(): Response
    {
        return $this->render("company/index.html.twig");
    }
}
