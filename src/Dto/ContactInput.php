<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Data of the contact form. Not readonly: the Form component writes the submitted values.
 */
final class ContactInput
{
    #[Assert\NotBlank(message: 'Merci d\'indiquer votre nom.')]
    #[Assert\Length(max: 100)]
    public ?string $name = null;

    #[Assert\NotBlank(message: 'Merci d\'indiquer votre e-mail.')]
    #[Assert\Email(message: 'Cette adresse e-mail n\'est pas valide.')]
    public ?string $email = null;

    #[Assert\NotBlank(message: 'Merci d\'indiquer un sujet.')]
    #[Assert\Length(max: 150)]
    public ?string $subject = null;

    #[Assert\NotBlank(message: 'Merci d\'écrire un message.')]
    #[Assert\Length(min: 10, max: 5000, minMessage: 'Votre message est un peu court.')]
    public ?string $message = null;

    // Honeypot: hidden from humans, bots fill it in.
    public ?string $website = null;

    public function isSpam(): bool
    {
        return null !== $this->website && '' !== $this->website;
    }
}
