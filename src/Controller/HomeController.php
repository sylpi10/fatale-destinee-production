<?php

namespace App\Controller;

use Nytodev\InertiaBundle\Service\Inertia;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    public function __construct(
        private readonly Inertia $inertia,
    ) {}

    #[Route("/", name: "app_home")]
    public function index(): Response
    {
        return $this->inertia->render("Home");
    }
}
