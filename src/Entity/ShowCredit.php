<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ShowCreditRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * One line of the "Distribution" block of a show: "De et avec : Teddy Moulin".
 */
#[ORM\Entity(repositoryClass: ShowCreditRepository::class)]
class ShowCredit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'credits')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Show $show = null;

    /** "De et avec", "Regards extérieurs"… */
    #[ORM\Column(length: 80)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 80)]
    private ?string $role = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    private ?string $people = null;

    #[ORM\Column]
    private int $position = 0;

    public function __toString(): string
    {
        return sprintf('%s : %s', $this->role, $this->people);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getShow(): ?Show
    {
        return $this->show;
    }

    public function setShow(?Show $show): static
    {
        $this->show = $show;

        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(?string $role): static
    {
        $this->role = $role;

        return $this;
    }

    public function getPeople(): ?string
    {
        return $this->people;
    }

    public function setPeople(?string $people): static
    {
        $this->people = $people;

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
}
