<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Controller\Admin\ShowCrudController;
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
        $user = (new User())
            ->setUsername('admin')
            ->setRoles(['ROLE_ADMIN']);
        $user->setPassword($this->passwordHasher->hashPassword($user, 'admin1234'));
        $manager->persist($user);

        // Content of the client brief (Fatale destinée.pdf).
        $teddy = (new Show())
            ->setTitle('Qu\'est-ce que je fous là ?')
            ->setSlug('qu-est-ce-que-je-fous-la')
            ->setPublished(true)
            ->setPosition(1)
            ->setAuthor('Teddy Moulin')
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
            ->setImage($this->copyImage('qu-est-ce-que-je-fous-la.webp'))
            ->addCredit((new ShowCredit())->setRole('De et avec')->setPeople('Teddy Moulin')->setPosition(1))
            ->addCredit((new ShowCredit())->setRole('Production')->setPeople('Fatale Destinée Production')->setPosition(5));
        $manager->persist($teddy);

        // Title and content to come.
        $angeline = (new Show())
            ->setTitle('Girouette Manigance')
            ->setSlug('girouette-manigance')
            ->setPublished(true)
            ->setPosition(2)
            ->setAuthor('Angéline Delourme')
            ->setGenre('Clown')
            ->setImage($this->copyImage('angeline-solo.webp'));
        $manager->persist($angeline);

        $manager->flush();
    }

    /**
     * The sample photos are versioned in public/images; uploads live in public/uploads.
     */
    private function copyImage(string $fileName): string
    {
        new Filesystem()->copy(
            $this->publicDir.'/images/spectacles/'.$fileName,
            $this->publicDir.'/'.ShowCrudController::UPLOAD_PATH.'/'.$fileName,
        );

        return $fileName;
    }
}
