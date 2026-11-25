<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Company;
use App\Image\WebpConverter;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * The single company row: the menu links straight to its edit page.
 *
 * @extends AbstractCrudController<Company>
 */
final class CompanyCrudController extends AbstractCrudController
{
    public const string UPLOAD_PATH = 'uploads/compagnie';

    public function __construct(
        private readonly WebpConverter $webpConverter,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Company::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Compagnie')
            ->setEntityLabelInPlural('Compagnie')
            ->setPageTitle(Crud::PAGE_EDIT, 'La compagnie');
    }

    public function configureActions(Actions $actions): Actions
    {
        $viewOnSite = Action::new('viewOnSite', 'Voir sur le site', 'fa fa-eye')
            ->linkToRoute('app_company')
            ->setHtmlAttributes(['target' => '_blank']);
        $viewLegalNotice = Action::new('viewLegalNotice', 'Voir les mentions légales', 'fa fa-scale-balanced')
            ->linkToRoute('app_legal')
            ->setHtmlAttributes(['target' => '_blank']);

        return $actions
            ->disable(Action::NEW, Action::DELETE, Action::BATCH_DELETE)
            ->add(Crud::PAGE_EDIT, $viewOnSite)
            ->add(Crud::PAGE_EDIT, $viewLegalNotice)
            ->remove(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN);
    }

    public function configureFields(string $pageName): iterable
    {
        yield ImageField::new('image', 'Image')
            ->setBasePath(self::UPLOAD_PATH)
            ->setUploadDir('public/'.self::UPLOAD_PATH)
            ->setUploadedFileNamePattern('[slug]-[randomhash].webp')
            ->setFormTypeOption('upload_new', $this->webpConverter)
            ->mimeTypes('image/jpeg,image/png,image/gif,image/webp', 'JPEG, PNG, GIF ou WebP uniquement.')
            ->setHelp('À côté du texte de présentation. Vide : le logo.');
        yield TextField::new('imageAlt', 'Description de l’image')
            ->setHelp('Ce que montre l’image, pour les malvoyants et Google (texte alternatif). Vide : image décorative, ignorée par les lecteurs d’écran.');
        yield TextareaField::new('presentation', 'Présentation')
            ->setNumOfRows(14)
            ->setHelp('Séparer les paragraphes par une ligne vide.');

        yield FormField::addFieldset('Mentions légales')
            ->setHelp('Informations obligatoires de la page « Mentions légales ». Une ligne vide n’est pas affichée.');
        yield TextField::new('legalForm', 'Forme juridique')
            ->hideOnIndex()
            ->setHelp('Par exemple : association loi 1901.');
        yield TextField::new('registrationNumber', 'SIRET ou n° RNA')
            ->hideOnIndex()
            ->setHelp('Le SIRET (14 chiffres), ou le numéro RNA (W…) d’une association sans SIRET.');
        yield TextareaField::new('address', 'Adresse du siège')
            ->hideOnIndex()
            ->setNumOfRows(3);
        yield TextField::new('publicationDirector', 'Directeur·rice de la publication')
            ->hideOnIndex()
            ->setHelp('Nom de la personne responsable du contenu du site, en général le ou la président·e.');
    }
}
