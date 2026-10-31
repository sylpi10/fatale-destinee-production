<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\CompanyRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

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

    /** Paragraphs separated by a blank line. */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $presentation = null;

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

    public function getPresentation(): ?string
    {
        return $this->presentation;
    }

    public function setPresentation(?string $presentation): static
    {
        $this->presentation = $presentation;

        return $this;
    }
}
