<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Show;
use App\Repository\ShowRepository;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ShowController extends AbstractController
{
    #[Route("/spectacles", name: "app_shows")]
    public function index(ShowRepository $showRepository): Response
    {
        return $this->render("show/index.html.twig", [
            "shows" => $showRepository->findPublished(),
        ]);
    }

    #[Route("/spectacles/{slug}", name: "app_show")]
    public function show(
        #[MapEntity(expr: "repository.findOnePublishedBySlug(slug)", message: "Spectacle introuvable.")] Show $show,
    ): Response {
        return $this->render("show/show.html.twig", [
            "show" => $show,
        ]);
    }
}
