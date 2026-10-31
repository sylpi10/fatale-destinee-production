<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Show;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FileField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;

/**
 * @extends AbstractCrudController<Show>
 */
final class ShowCrudController extends AbstractCrudController
{
    public const string UPLOAD_PATH = 'uploads/spectacles';

    public static function getEntityFqcn(): string
    {
        return Show::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Spectacle')
            ->setEntityLabelInPlural('Spectacles')
            ->setDefaultSort(['position' => 'ASC', 'title' => 'ASC'])
            ->setSearchFields(['title', 'author', 'genre']);
    }

    public function configureActions(Actions $actions): Actions
    {
        $viewOnSite = Action::new('viewOnSite', 'Voir sur le site', 'fa fa-eye')
            ->linkToRoute('app_show', static fn (Show $show): array => ['slug' => $show->getSlug()])
            ->setHtmlAttributes(['target' => '_blank'])
            ->displayIf(static fn (Show $show): bool => $show->isPublished());

        return $actions
            ->add(Crud::PAGE_INDEX, $viewOnSite)
            ->add(Crud::PAGE_EDIT, $viewOnSite);
    }

    public function configureFields(string $pageName): iterable
    {
        yield FormField::addTab('Le spectacle');
        yield TextField::new('title', 'Titre');
        yield SlugField::new('slug', 'Adresse')
            ->setTargetFieldName('title')
            ->setHelp('Fin de l’adresse de la page : /spectacles/…')
            ->hideOnIndex();
        yield ImageField::new('image', 'Photo principale')
            ->setBasePath(self::UPLOAD_PATH)
            ->setUploadDir('public/'.self::UPLOAD_PATH)
            ->setUploadedFileNamePattern('[slug]-[randomhash].[extension]')
            ->setHelp('En haut de la page et dans la liste des spectacles.');
        yield TextField::new('author', 'Porté par')->setHelp('Ex. : Teddy Moulin');
        yield BooleanField::new('published', 'Publié')->renderAsSwitch();
        yield IntegerField::new('position', 'Ordre')->setHelp('Ordre dans la liste, du plus petit au plus grand.');

        yield FormField::addFieldset('Pastilles')->hideOnIndex();
        yield TextField::new('genre', 'Genre')->setHelp('Ex. : Clown')->hideOnIndex();
        yield IntegerField::new('creationYear', 'Année de création')->hideOnIndex();
        yield IntegerField::new('minimumAge', 'À partir de (âge)')->hideOnIndex();
        yield TextField::new('venues', 'Lieux')->setHelp('Ex. : EXT / INT')->hideOnIndex();
        yield IntegerField::new('durationMinutes', 'Durée (minutes)')->hideOnIndex();

        yield FormField::addTab('Textes et teaser');
        yield TextareaField::new('catchphrase', 'Phrase d’accroche')->hideOnIndex();
        yield TextareaField::new('synopsis', 'Synopsis')
            ->setNumOfRows(10)
            ->setHelp('Séparer les paragraphes par une ligne vide.')
            ->hideOnIndex();
        yield UrlField::new('teaserUrl', 'Teaser')
            ->setHelp('Lien « intégrer » de la vidéo (YouTube : https://www.youtube.com/embed/…, Vimeo : https://player.vimeo.com/video/…).')
            ->hideOnIndex();

        yield FormField::addTab('Distribution');
        yield CollectionField::new('credits', 'Distribution')
            ->useEntryCrudForm(ShowCreditCrudController::class)
            ->allowAdd()
            ->allowDelete()
            ->setHelp('Ex. : « De et avec » → Teddy Moulin.')
            ->hideOnIndex();

        yield FormField::addTab('Espace pro');
        foreach ([
            'pressKit' => 'Dossier de diffusion',
            'technicalRider' => 'Fiche technique',
            'standsTechnicalRider' => 'Fiche technique gradins',
            'pressPhotos' => 'Photos communication',
        ] as $property => $label) {
            yield FileField::new($property, $label)
                ->setBasePath(self::UPLOAD_PATH)
                ->setUploadDir('public/'.self::UPLOAD_PATH)
                ->setUploadedFileNamePattern('[slug]-[randomhash].[extension]')
                ->mimeTypes('application/pdf,application/zip', 'PDF ou ZIP uniquement.')
                ->maxSize('50M')
                ->hideOnIndex();
        }
    }
}
