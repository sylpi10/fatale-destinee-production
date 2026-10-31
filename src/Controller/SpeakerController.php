<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SpeakerController extends AbstractController
{
    // Static content for now, copied from the current site: it moves to the database with the admin.
    #[Route("/intervenant", name: "app_speaker")]
    public function index(): Response
    {
        return $this->render("speaker/index.html.twig");
    }
}
