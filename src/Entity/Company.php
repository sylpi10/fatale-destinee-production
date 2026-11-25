<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\CompanyRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * The company itself, top of the Compagnie page (sketch 2). Only one row.
 */
#[ORM\Entity(repositoryClass: CompanyRepository::class)]
class Company
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /** Next to the presentation; the logo when empty. File name in public/uploads/compagnie. */
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    /** Alternative text of the image: what it shows, for screen readers and search engines. */
    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $imageAlt = null;

    /** Paragraphs separated by a blank line. */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $presentation = null;

    /** Legal notice (LCEN): association, SARL… */
    #[ORM\Column(length: 100, nullable: true)]
    #[Assert\Length(max: 100)]
    private ?string $legalForm = null;

    /** Legal notice: SIRET, or RNA number for an association without one. */
    #[ORM\Column(length: 50, nullable: true)]
    #[Assert\Length(max: 50)]
    private ?string $registrationNumber = null;

    /** Legal notice: registered office, one line per row of the address. */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(max: 500)]
    private ?string $address = null;

    /** Legal notice: the person legally responsible for what the site publishes. */
    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $publicationDirector = null;

    public function __toString(): string
    {
        return 'La compagnie';
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getImageAlt(): ?string
    {
        return $this->imageAlt;
    }

    public function setImageAlt(?string $imageAlt): static
    {
        $this->imageAlt = $imageAlt;

        return $this;
    }

    public function getPresentation(): ?string
    {
        return $this->presentation;
    }

    public function setPresentation(?string $presentation): static
    {
        $this->presentation = $presentation;

        return $this;
    }

    public function getLegalForm(): ?string
    {
        return $this->legalForm;
    }

    public function setLegalForm(?string $legalForm): static
    {
        $this->legalForm = $legalForm;

        return $this;
    }

    public function getRegistrationNumber(): ?string
    {
        return $this->registrationNumber;
    }

    public function setRegistrationNumber(?string $registrationNumber): static
    {
        $this->registrationNumber = $registrationNumber;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getPublicationDirector(): ?string
    {
        return $this->publicationDirector;
    }

    public function setPublicationDirector(?string $publicationDirector): static
    {
        $this->publicationDirector = $publicationDirector;

        return $this;
    }
}
