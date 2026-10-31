<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: "UNIQ_IDENTIFIER_USERNAME", fields: ["username"])]
#[UniqueEntity(fields: "username", message: "Ce pseudo est déjà pris.")]
/**
 * An account of the admin, and/or a member of the company shown on the Compagnie page
 * (sketch 3). A member without a password can't log in.
 */
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /** Login of the account (there is no email: accounts are created in the console). */
    #[ORM\Column(length: 50)]
    #[Assert\NotBlank]
    #[Assert\Regex("/^[\\w.-]{3,50}$/u", message: "3 à 50 lettres, chiffres, points, tirets ou soulignés.")]
    private ?string $username = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string|null The hashed password; null: the account can't log in
     */
    #[ORM\Column(nullable: true)]
    private ?string $password = null;

    #[ORM\Column(length: 80, nullable: true)]
    #[Assert\Length(max: 80)]
    private ?string $firstName = null;

    #[ORM\Column(length: 80, nullable: true)]
    #[Assert\Length(max: 80)]
    private ?string $lastName = null;

    /** Portrait, file name in public/uploads/compagnie. */
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo = null;

    /** Paragraphs separated by a blank line. */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $bio = null;

    /** Shown on the Compagnie page. */
    #[ORM\Column(options: ['default' => false])]
    private bool $member = false;

    /** Order on the Compagnie page, smallest first. */
    #[ORM\Column(options: ['default' => 0])]
    private int $position = 0;

    /** @var Collection<int, Show> */
    #[ORM\OneToMany(targetEntity: Show::class, mappedBy: 'member')]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private Collection $shows;

    public function __construct()
    {
        $this->shows = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->getFullName() ?? (string) $this->username;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = trim($username);

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = "ROLE_USER";

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Ensure the session doesn't contain actual password hashes by CRC32C-hashing them, as supported since Symfony 7.3.
     */
    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0" . self::class . "\0password"] = hash(
            "crc32c",
            (string) $this->password,
        );
        // Not needed in the session, and would load every show.
        unset($data["\0" . self::class . "\0shows"]);

        return $data;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getFullName(): ?string
    {
        $fullName = trim(sprintf('%s %s', $this->firstName, $this->lastName));

        return '' === $fullName ? null : $fullName;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): static
    {
        $this->photo = $photo;

        return $this;
    }

    public function getBio(): ?string
    {
        return $this->bio;
    }

    public function setBio(?string $bio): static
    {
        $this->bio = $bio;

        return $this;
    }

    public function isMember(): bool
    {
        return $this->member;
    }

    public function setMember(bool $member): static
    {
        $this->member = $member;

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

    /**
     * @return Collection<int, Show>
     */
    public function getShows(): Collection
    {
        return $this->shows;
    }

    public function addShow(Show $show): static
    {
        if (!$this->shows->contains($show)) {
            $this->shows->add($show);
            $show->setMember($this);
        }

        return $this;
    }

    public function removeShow(Show $show): static
    {
        if ($this->shows->removeElement($show) && $show->getMember() === $this) {
            $show->setMember(null);
        }

        return $this;
    }
}
