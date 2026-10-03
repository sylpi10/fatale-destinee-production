<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\Page\PhotosPageProps;
use Nytodev\InertiaBundle\Service\Inertia;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PhotoController extends AbstractController
{
    private const string PHOTOS_DIR = "/images/photos";

    public function __construct(
        private readonly Inertia $inertia,
        #[Autowire("%kernel.project_dir%/public")] private readonly string $publicDir,
    ) {}

    // Photos are read from public/images/photos for now: they move to the database with MySQL.
    #[Route("/photos", name: "app_photos")]
    public function index(): Response
    {
        $files = glob($this->publicDir . self::PHOTOS_DIR . "/*.jpg") ?: [];
        sort($files);

        return $this->inertia->render(
            "Photos",
            get_object_vars(
                new PhotosPageProps(
                    title: "Photos de spectacle",
                    caption: "Photos de spectacle 2025 / Cabaret d'Hanokh Levin / représentation au théâtre de la Chocolaterie / Crédits : Elon Cattan",
                    photos: array_map(
                        fn (string $file): string => self::PHOTOS_DIR . "/" . basename($file),
                        $files,
                    ),
                ),
            ),
        );
    }
}
