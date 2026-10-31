<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class WorkshopController extends AbstractController
{
    // Static content for now, copied from the current site: it moves to the database with the admin.
    #[Route("/ateliers", name: "app_workshops")]
    public function index(): Response
    {
        return $this->render("workshop/index.html.twig");
    }
}
