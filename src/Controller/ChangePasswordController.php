<?php
namespace App\Controller;

use App\Entity\PasswordUpdate;
use App\Form\PasswordUpdateType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ChangePasswordController extends AbstractController
{
    #[Route("/change-password", name:'change_password')]
    public function changePassword(Request $request, EntityManagerInterface $manager, UserPasswordHasherInterface $hasher): Response
    {
        $passwordUpdate = new PasswordUpdate();
        $user = $this->getUser();
        $form = $this->createForm(PasswordUpdateType::class, $passwordUpdate);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Check if the password matches the current password
            if (!password_verify($passwordUpdate->getOldPassword(), $user->getPassword())) {
                // Add a form error if the password is incorrect
                $form->get('oldPassword')->addError(new FormError("Le mot de passe que vous avez tapé n'est pas votre mot de passe actuel"));
            } else {
                $newPassword = $passwordUpdate->getNewPassword();
                $hashedPassword = $hasher->hashPassword($user, $newPassword);

                $user->setPassword($hashedPassword);
                $manager->persist($user);
                $manager->flush();

                $this->addFlash(
                    'success',
                    "Votre mot de passe a bien été modifié"
                );

                return $this->redirectToRoute('app_home');
            }
        }

        return $this->render("change_password/change_password.html.twig", [
            'form' => $form->createView()
        ]);
    }
}
