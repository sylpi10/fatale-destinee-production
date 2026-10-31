<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Repository\CompanyRepository;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
#[IsGranted('ROLE_ADMIN')]
final class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private readonly CompanyRepository $companyRepository,
    ) {}

    public function index(): Response
    {
        // Nothing to show on a home page yet: go straight to the shows.
        return $this->redirectToRoute('admin_show_index');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Fatale Destinée Production')
            ->setFaviconPath('images/favicon.png')
            ->setLocales(['fr']);
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkTo(CompanyCrudController::class, 'La compagnie', 'fa fa-building-columns')
            ->setAction(Action::EDIT)
            ->setEntityId((int) $this->companyRepository->get()->getId());
        yield MenuItem::linkTo(UserCrudController::class, 'Membres et comptes', 'fa fa-users');
        yield MenuItem::linkTo(ShowCrudController::class, 'Spectacles', 'fa fa-masks-theater');
        yield MenuItem::section();
        yield MenuItem::linkToRoute('Voir le site', 'fa fa-arrow-up-right-from-square', 'app_home');
        yield MenuItem::linkToLogout('Déconnexion', 'fa fa-right-from-bracket');
    }
}
