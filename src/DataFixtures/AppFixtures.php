<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Company;
use App\Entity\Show;
use App\Entity\ShowCredit;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        #[Autowire('%kernel.project_dir%/public')] private string $publicDir,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $admin = (new User())
            ->setUsername('admin')
            ->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin1234'));
        $manager->persist($admin);

        // Content of the client brief (Fatale destinée.pdf).
        $manager->persist((new Company())->setPresentation(<<<'TXT'
            Fatale Destinée Production (FDP) est créée en 2025 par Angéline Delourme et Teddy Moulin. Elle développe un travail où l’absurde devient un outil pour interroger le sens de la vie et la place de l’individu dans le monde contemporain.

            Sa démarche artistique s’appuie sur une écriture scénique mêlant humour décalé, détournements poétiques et situations apparemment dérisoires, qui révèlent pourtant une profondeur intime. L’absurde n’y est pas une fin en soi, mais un langage permettant de bousculer les évidences, d’ouvrir des espaces de réflexions et d’inviter le public à porter un regard renouvelé sur le réel.

            À travers ses créations, FDP cherche à construire des formes sensibles et visuellement marquées, où l’humour fonctionne comme un vecteur d’émotions autant que de pensées.
            TXT));

        // Members without password: they don't log in until an admin account is set up for them.
        $teddy = (new User())
            ->setUsername('teddy')
            ->setFirstName('Teddy')
            ->setLastName('Moulin')
            ->setMember(true)
            ->setPosition(1)
            ->setPhoto($this->copy('compagnie', 'teddy-moulin.webp'))
            ->setBio(<<<'TXT'
                Teddy Moulin fait du clown parce que c’est l’endroit où il est encore possible de rater correctement.

                Formé au théâtre à l’école de la Compagnie Maritime à Montpellier, puis au clown avec Christine Rossignol Dallaire, Alexandre Pavlata, Emmanuel Sembely, Arnaud Aymard, ainsi qu’à différentes techniques plus ou moins reconnues (chute, doute, enthousiasme mal placé), il développe une pratique du clown basée sur l’échec persistant et la tentative répétée d’aller mieux — sans preuve que ce soit une bonne idée.

                Il tente de comprendre ce qu’il fait là, en fabriquant des spectacles où des personnages fragiles prennent de très mauvaises décisions avec beaucoup de conviction.

                Son travail s’intéresse à des sujets simples : croire, obéir, espérer, recommencer… et parfois s’arrêter, mais rarement au bon moment.

                Sur scène et dans la rue, il met en jeu des figures instables, influençables, souvent dépassées par des forces qui les rassurent autant qu’elles les écrasent. Des figures qui ressemblent, de loin, à nous.
                TXT);
        $manager->persist($teddy);

        $angeline = (new User())
            ->setUsername('angeline')
            ->setFirstName('Angéline')
            ->setLastName('Delourme')
            ->setMember(true)
            ->setPosition(2)
            ->setPhoto($this->copy('compagnie', 'angeline-delourme.webp'))
            ->setBio(<<<'TXT'
                Angéline Delourme se forme au métier de comédienne et de chanteuse au conservatoire de Perpignan de 2016 à 2020. Entre 2020 et 2025, elle approfondit sa pratique en explorant différentes méthodes de jeu : l’école du jeu à Paris, le Samovar (clown) à Bagnolet, une formation intensive de théâtre physique à Montpellier.

                En parallèle de cet acte de formation continue, elle met en scène et joue des spectacles pour les enfants, et crée des solos/duos de clown pour l’univers du cabaret. En 2025, elle co-crée la forme longue d’un duo clowne/dragqueen, « Mâdame ».

                Ce projet, autour de sa clowne Girouette Manigance, est sa première création en solo. Elle y mélange l’art du clown, du théâtre et du chant.
                TXT);
        $manager->persist($angeline);

        $manager->persist((new Show())
            ->setTitle('Qu\'est-ce que je fous là ?')
            ->setSlug('qu-est-ce-que-je-fous-la')
            ->setPublished(true)
            ->setPosition(1)
            ->setMember($teddy)
            ->setGenre('Clown')
            ->setCreationYear(2026)
            ->setMinimumAge(8)
            ->setVenues('EXT / INT')
            ->setDurationMinutes(55)
            ->setCatchphrase('Moroze cherche sa place. Jean-Michel Bonheur a réponse à tout.')
            ->setSynopsis(
                "Entre crises existentielles, rêves d’écureuils mignons et vérités douteuses, une question demeure : et si le problème, c’était simplement d’être là ?\n\n"
                ."Un solo plus ou moins autobiographique, bien que fictionnel, à plusieurs, et vaguement inspiré de faits qui n'ont probablement jamais eu lieu."
            )
            ->setImage($this->copy('spectacles', 'qu-est-ce-que-je-fous-la.webp'))
            ->addCredit((new ShowCredit())->setRole('De et avec')->setPeople('Teddy Moulin')->setPosition(1))
            ->addCredit((new ShowCredit())->setRole('Production')->setPeople('Fatale Destinée Production')->setPosition(5)));

        // Title and content to come.
        $manager->persist((new Show())
            ->setTitle('Girouette Manigance')
            ->setSlug('girouette-manigance')
            ->setPublished(true)
            ->setPosition(2)
            ->setMember($angeline)
            ->setGenre('Clown')
            ->setImage($this->copy('spectacles', 'angeline-solo.webp')));

        $manager->flush();
    }

    /**
     * The sample photos are versioned in public/images; uploads live in public/uploads.
     */
    private function copy(string $directory, string $fileName): string
    {
        new Filesystem()->copy(
            $this->publicDir.'/images/'.$directory.'/'.$fileName,
            $this->publicDir.'/uploads/'.$directory.'/'.$fileName,
        );

        return $fileName;
    }
}
