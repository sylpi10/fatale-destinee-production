<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\Page\WorkshopsPageProps;
use App\Dto\WorkshopSlotDto;
use Nytodev\InertiaBundle\Service\Inertia;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class WorkshopController extends AbstractController
{
    public function __construct(
        private readonly Inertia $inertia,
    ) {}

    // Static content for now, copied from the current site: it moves to the database with MySQL.
    #[Route("/ateliers", name: "app_workshops")]
    public function index(): Response
    {
        return $this->inertia->render(
            "Workshops",
            get_object_vars(
                new WorkshopsPageProps(
                    intro: "Organisés par la Compagnie Domovoi, les ateliers se déroulent en deux groupes distincts.",
                    slots: [
                        new WorkshopSlotDto(
                            day: "Mardi",
                            hours: "20h – 22h",
                            place: "Salle des Arceaux, 14 bis rue Saint-Louis",
                        ),
                        new WorkshopSlotDto(
                            day: "Jeudi",
                            hours: "19h30 – 21h30",
                            place: "16 rue Dom Vaissette",
                        ),
                    ],
                    schedulingNotes: [
                        "Premier cours le 16 et 18 septembre.",
                        "Cours hebdomadaire (hors vacances scolaires).",
                    ],
                    content: [
                        "Travail du lâcher-prise, de l'improvisation, du corps, de l'espace, du rythme dans une première partie de l'année.",
                        "Travail du texte dans un second temps et mise en scène d'un spectacle à partir de janvier. Plusieurs représentations en juin.",
                    ],
                    price: "400 € l'année",
                    priceNote: "Paiement en plusieurs fois possible.",
                ),
            ),
        );
    }
}
