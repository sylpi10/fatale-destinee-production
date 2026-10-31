<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Dto\ChangePasswordInput;
use App\Entity\User;
use App\Form\ChangePasswordType;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Lets the logged-in user change their own password, inside the admin layout.
 */
#[IsGranted('ROLE_ADMIN')]
final class PasswordController extends AbstractController
{
    #[AdminRoute('/mot-de-passe', name: 'password', options: ['methods' => ['GET', 'POST']])]
    public function __invoke(
        Request $request,
        #[CurrentUser] User $user,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
    ): Response {
        $input = new ChangePasswordInput();
        $form = $this->createForm(ChangePasswordType::class, $input);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($passwordHasher->hashPassword($user, (string) $input->newPassword));
            // The session is saved after this request with the new password: the user stays logged in.
            $entityManager->flush();

            $this->addFlash('success', 'Votre mot de passe a été modifié.');

            return $this->redirectToRoute('admin_password');
        }

        return $this->render('admin/password.html.twig', [
            'form' => $form,
        ], new Response(status: $form->isSubmitted() ? 422 : 200));
    }
}
