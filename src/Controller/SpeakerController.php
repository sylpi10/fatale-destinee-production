<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\Page\SpeakerPageProps;
use Nytodev\InertiaBundle\Service\Inertia;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SpeakerController extends AbstractController
{
    public function __construct(
        private readonly Inertia $inertia,
    ) {}

    // Static content for now, copied from the current site: it moves to the database with MySQL.
    #[Route("/intervenant", name: "app_speaker")]
    public function index(): Response
    {
        return $this->inertia->render(
            "Speaker",
            get_object_vars(
                new SpeakerPageProps(
                    name: "Teddy Moulin",
                    photo: "/images/teddy-moulin.jpg",
                    bio: [
                        "Teddy Moulin se forme au métier de comédien à l'école de la Compagnie Maritime de 2017 à 2020. Puis il explore les méthodes de jeu américaines avec Robert Castle (IT New York) et Jordan Beswick. Entre 2020 et 2025, il se forme à l'art du clown avec Eric Blouet, Alexandre Pavlata, Emmanuel Sembely, Elise Ouvrier-Buffet, Arnaud Aymard et Barbara Gay.",
                        "Il travaille au théâtre avec la Compagnie Domovoï sur le spectacle Le Plancher Des Vaches. Il travaille sur les plateaux de cinéma et de télévision dans divers films et séries, dont récemment la série américaine « Mister Spade » de Scott Frank (Canal +), et le long métrage « 15/18 » de Cédric Kahn (sortie prévue en 2026).",
                    ],
                ),
            ),
        );
    }
}
