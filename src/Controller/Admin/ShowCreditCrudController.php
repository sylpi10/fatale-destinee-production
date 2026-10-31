<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\ShowCredit;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * Only used to edit the credits inside the form of a show.
 *
 * @extends AbstractCrudController<ShowCredit>
 */
final class ShowCreditCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ShowCredit::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('role', 'Rôle')->setHelp('Ex. : De et avec, Regards extérieurs…');
        yield TextareaField::new('people', 'Noms')->setNumOfRows(2);
        yield IntegerField::new('position', 'Ordre');
    }
}
