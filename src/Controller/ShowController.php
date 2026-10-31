<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ShowController extends AbstractController
{
    // Placeholder until the content of this page is briefed.
    #[Route("/spectacles", name: "app_shows")]
    public function index(): Response
    {
        return $this->render("show/index.html.twig");
    }
}
