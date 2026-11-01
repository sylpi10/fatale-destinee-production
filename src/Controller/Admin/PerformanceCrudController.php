<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Performance;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * Only used to edit the dates inside the form of a show.
 *
 * @extends AbstractCrudController<Performance>
 */
final class PerformanceCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Performance::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield DateField::new('date', 'Date');
        yield TextField::new('place', 'Lieu')->setHelp('Ex. : Montpellier, ou Théâtre Jean Vilar, Montpellier');
        yield TextField::new('event', 'Événement')->setHelp('Facultatif. Ex. : Festival de rue de Ramonville');
    }
}
