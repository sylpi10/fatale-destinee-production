<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Company;
use App\Image\WebpConverter;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;

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

        return $actions
            ->disable(Action::NEW, Action::DELETE, Action::BATCH_DELETE)
            ->add(Crud::PAGE_EDIT, $viewOnSite)
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
        yield TextareaField::new('presentation', 'Présentation')
            ->setNumOfRows(14)
            ->setHelp('Séparer les paragraphes par une ligne vide.');
    }
}
