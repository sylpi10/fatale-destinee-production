<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\User;
use App\Image\WebpConverter;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * Members of the company (Compagnie page) and admin accounts: the same entity.
 *
 * @extends AbstractCrudController<User>
 */
final class UserCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly WebpConverter $webpConverter,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Membre')
            ->setEntityLabelInPlural('Membres et comptes')
            ->setDefaultSort(['member' => 'DESC', 'position' => 'ASC', 'lastName' => 'ASC'])
            ->setSearchFields(['username', 'firstName', 'lastName']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield FormField::addTab('Membre de la compagnie');
        yield ImageField::new('photo', 'Portrait')
            ->setBasePath(CompanyCrudController::UPLOAD_PATH)
            ->setUploadDir('public/'.CompanyCrudController::UPLOAD_PATH)
            ->setUploadedFileNamePattern('[slug]-[randomhash].webp')
            ->setFormTypeOption('upload_new', $this->webpConverter)
            ->mimeTypes('image/jpeg,image/png,image/gif,image/webp', 'JPEG, PNG, GIF ou WebP uniquement.');
        yield TextField::new('firstName', 'Prénom')->hideOnIndex();
        yield TextField::new('lastName', 'Nom')->hideOnIndex();
        yield TextField::new('fullName', 'Nom')->onlyOnIndex();
        yield BooleanField::new('member', 'Sur la page Compagnie')->renderAsSwitch();
        yield IntegerField::new('position', 'Ordre')->setHelp('Ordre sur la page Compagnie, du plus petit au plus grand.');
        yield TextareaField::new('bio', 'Présentation')
            ->setNumOfRows(12)
            ->setHelp('Séparer les paragraphes par une ligne vide.')
            ->hideOnIndex();
        yield AssociationField::new('shows', 'Spectacles')
            ->onlyOnIndex();

        yield FormField::addTab('Compte');
        yield TextField::new('username', 'Pseudo')
            ->setHelp('Pour se connecter. Le premier mot de passe se définit en console : php bin/console app:admin:create <pseudo> ; ensuite, chacun le change dans « Mon mot de passe ». Sans mot de passe, le compte ne peut pas se connecter.');
        yield ChoiceField::new('roles', 'Droits')
            ->setChoices(['Administrateur' => 'ROLE_ADMIN'])
            ->allowMultipleChoices()
            ->renderExpanded()
            ->renderAsBadges();
    }
}
