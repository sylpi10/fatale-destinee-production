<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\CompanyRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CompanyController extends AbstractController
{
    #[Route("/compagnie", name: "app_company")]
    public function index(CompanyRepository $companyRepository, UserRepository $userRepository): Response
    {
        return $this->render("company/index.html.twig", [
            "company" => $companyRepository->get(),
            "members" => $userRepository->findMembers(),
        ]);
    }
}
