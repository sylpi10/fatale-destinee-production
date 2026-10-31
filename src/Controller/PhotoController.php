<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PhotoController extends AbstractController
{
    private const string PHOTOS_DIR = "images/photos";

    public function __construct(
        #[Autowire("%kernel.project_dir%/public")] private readonly string $publicDir,
    ) {}

    // Photos are read from public/images/photos for now: they move to the database with the admin.
    #[Route("/photos", name: "app_photos")]
    public function index(): Response
    {
        $files = glob($this->publicDir . "/" . self::PHOTOS_DIR . "/*.jpg") ?: [];
        sort($files);

        return $this->render("photo/index.html.twig", [
            "photos" => array_map(
                fn (string $file): string => self::PHOTOS_DIR . "/" . basename($file),
                $files,
            ),
        ]);
    }
}
