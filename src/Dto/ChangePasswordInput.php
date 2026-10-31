<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Security\Core\Validator\Constraints as SecurityAssert;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Data of the admin "Mon mot de passe" form. Not readonly: the Form component writes the submitted values.
 */
final class ChangePasswordInput
{
    #[Assert\NotBlank(message: 'Merci d\'indiquer votre mot de passe actuel.')]
    #[SecurityAssert\UserPassword(message: 'Ce n\'est pas votre mot de passe actuel.')]
    public ?string $currentPassword = null;

    // Same minimum as the app:admin:create command.
    #[Assert\NotBlank(message: 'Merci de choisir un nouveau mot de passe.')]
    #[Assert\Length(min: 8, max: 4096, minMessage: 'Au moins {{ limit }} caractères.')]
    public ?string $newPassword = null;
}
