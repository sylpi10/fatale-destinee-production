<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\PerformanceRepository;
use Psr\Clock\ClockInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AgendaController extends AbstractController
{
    #[Route("/agenda", name: "app_agenda")]
    public function index(PerformanceRepository $performanceRepository, ClockInterface $clock): Response
    {
        return $this->render("agenda/index.html.twig", [
            "performances" => $performanceRepository->findUpcoming($clock->now()),
        ]);
    }
}
