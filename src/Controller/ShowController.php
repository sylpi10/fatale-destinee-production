<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

// Static content for now, from the client brief: shows move to the database with the admin.
final class ShowController extends AbstractController
{
    #[Route("/spectacles", name: "app_shows")]
    public function index(): Response
    {
        return $this->render("show/index.html.twig");
    }

    #[Route("/spectacles/{slug}", name: "app_show", requirements: ["slug" => "qu-est-ce-que-je-fous-la"])]
    public function show(): Response
    {
        return $this->render("show/show.html.twig");
    }
}
