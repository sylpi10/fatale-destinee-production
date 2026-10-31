<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ShowRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A show of the company, as on the show page of the brief (sketches 5, 5' and 5'').
 * "show" is a reserved word in MySQL, hence the table name.
 */
#[ORM\Entity(repositoryClass: ShowRepository::class)]
#[ORM\Table(name: 'theater_show')]
#[UniqueEntity(fields: 'slug', message: 'Un autre spectacle utilise déjà cette adresse.')]
class Show
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 150)]
    private ?string $title = null;

    #[ORM\Column(length: 160, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', message: 'Seulement des minuscules, des chiffres et des tirets.')]
    private ?string $slug = null;

    /** Shown in the list and on the page. */
    #[ORM\Column]
    private bool $published = false;

    /** Order in the list of shows, smallest first. */
    #[ORM\Column]
    private int $position = 0;

    /** Who carries the show: Teddy Moulin, Angéline Delourme… */
    #[ORM\Column(length: 150, nullable: true)]
    private ?string $author = null;

    /** "Clown" */
    #[ORM\Column(length: 60, nullable: true)]
    private ?string $genre = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Range(min: 2000, max: 2100)]
    private ?int $creationYear = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Range(min: 0, max: 99)]
    private ?int $minimumAge = null;

    /** "EXT / INT" */
    #[ORM\Column(length: 40, nullable: true)]
    private ?string $venues = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Positive]
    private ?int $durationMinutes = null;

    /** Hand-written catchphrase (Caveat, red). */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $catchphrase = null;

    /** Paragraphs separated by a blank line. */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $synopsis = null;

    /** Embed URL of the teaser (YouTube or Vimeo "embed" link). */
    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Url(requireTld: true)]
    private ?string $teaserUrl = null;

    /** Main photo, file name in public/uploads/spectacles. */
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    // Documents of the "Espace pro", file names in public/uploads/spectacles.
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $pressKit = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $technicalRider = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $standsTechnicalRider = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $pressPhotos = null;

    /** @var Collection<int, ShowCredit> */
    #[ORM\OneToMany(targetEntity: ShowCredit::class, mappedBy: 'show', cascade: ['persist'], orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC'])]
    #[Assert\Valid]
    private Collection $credits;

    public function __construct()
    {
        $this->credits = new ArrayCollection();
    }

    public function __toString(): string
    {
        return (string) $this->title;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function isPublished(): bool
    {
        return $this->published;
    }

    public function setPublished(bool $published): static
    {
        $this->published = $published;

        return $this;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): static
    {
        $this->position = $position;

        return $this;
    }

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setAuthor(?string $author): static
    {
        $this->author = $author;

        return $this;
    }

    public function getGenre(): ?string
    {
        return $this->genre;
    }

    public function setGenre(?string $genre): static
    {
        $this->genre = $genre;

        return $this;
    }

    public function getCreationYear(): ?int
    {
        return $this->creationYear;
    }

    public function setCreationYear(?int $creationYear): static
    {
        $this->creationYear = $creationYear;

        return $this;
    }

    public function getMinimumAge(): ?int
    {
        return $this->minimumAge;
    }

    public function setMinimumAge(?int $minimumAge): static
    {
        $this->minimumAge = $minimumAge;

        return $this;
    }

    public function getVenues(): ?string
    {
        return $this->venues;
    }

    public function setVenues(?string $venues): static
    {
        $this->venues = $venues;

        return $this;
    }

    public function getDurationMinutes(): ?int
    {
        return $this->durationMinutes;
    }

    public function setDurationMinutes(?int $durationMinutes): static
    {
        $this->durationMinutes = $durationMinutes;

        return $this;
    }

    public function getCatchphrase(): ?string
    {
        return $this->catchphrase;
    }

    public function setCatchphrase(?string $catchphrase): static
    {
        $this->catchphrase = $catchphrase;

        return $this;
    }

    public function getSynopsis(): ?string
    {
        return $this->synopsis;
    }

    public function setSynopsis(?string $synopsis): static
    {
        $this->synopsis = $synopsis;

        return $this;
    }

    /**
     * @return list<string>
     */
    public function getSynopsisParagraphs(): array
    {
        return array_values(array_filter(
            array_map(trim(...), preg_split('/\R\s*\R/', (string) $this->synopsis) ?: []),
            static fn (string $paragraph): bool => '' !== $paragraph,
        ));
    }

    public function getTeaserUrl(): ?string
    {
        return $this->teaserUrl;
    }

    public function setTeaserUrl(?string $teaserUrl): static
    {
        $this->teaserUrl = $teaserUrl;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getPressKit(): ?string
    {
        return $this->pressKit;
    }

    public function setPressKit(?string $pressKit): static
    {
        $this->pressKit = $pressKit;

        return $this;
    }

    public function getTechnicalRider(): ?string
    {
        return $this->technicalRider;
    }

    public function setTechnicalRider(?string $technicalRider): static
    {
        $this->technicalRider = $technicalRider;

        return $this;
    }

    public function getStandsTechnicalRider(): ?string
    {
        return $this->standsTechnicalRider;
    }

    public function setStandsTechnicalRider(?string $standsTechnicalRider): static
    {
        $this->standsTechnicalRider = $standsTechnicalRider;

        return $this;
    }

    public function getPressPhotos(): ?string
    {
        return $this->pressPhotos;
    }

    public function setPressPhotos(?string $pressPhotos): static
    {
        $this->pressPhotos = $pressPhotos;

        return $this;
    }

    /**
     * Documents of the "Espace pro" that have a file, label => file name.
     *
     * @return array<string, string>
     */
    public function getProDocuments(): array
    {
        return array_filter([
            'Dossier de diffusion' => $this->pressKit,
            'Fiche technique' => $this->technicalRider,
            'Fiche technique gradins' => $this->standsTechnicalRider,
            'Photos communication' => $this->pressPhotos,
        ]);
    }

    /**
     * @return Collection<int, ShowCredit>
     */
    public function getCredits(): Collection
    {
        return $this->credits;
    }

    public function addCredit(ShowCredit $credit): static
    {
        if (!$this->credits->contains($credit)) {
            $this->credits->add($credit);
            $credit->setShow($this);
        }

        return $this;
    }

    public function removeCredit(ShowCredit $credit): static
    {
        if ($this->credits->removeElement($credit) && $credit->getShow() === $this) {
            $credit->setShow(null);
        }

        return $this;
    }
}
