<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\PerformanceRepository;
use App\Repository\ShowRepository;
use Psr\Clock\ClockInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Local search page ("compagnie de clown Montpellier"): who the company is, its shows,
 * its next dates and the workshops, in one page linked from the footer.
 */
final class LocalController extends AbstractController
{
    #[Route("/compagnie-clown-theatre-montpellier", name: "app_local")]
    public function index(
        ShowRepository $showRepository,
        PerformanceRepository $performanceRepository,
        ClockInterface $clock,
        #[Autowire("%env(CONTACT_EMAIL)%")] string $contactEmail,
    ): Response {
        return $this->render("local/index.html.twig", [
            "shows" => $showRepository->findPublished(),
            "performances" => $performanceRepository->findUpcoming($clock->now(), 5),
            "contact_email" => $contactEmail,
        ]);
    }
}
