<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ResetPasswordType;
use Symfony\Component\Uid\Ulid;
use App\Form\SendEmailResetType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


class LoginController extends AbstractController
{
   
    #[Route('/login', name: 'app_login')]
    public function login( AuthenticationUtils $authenticationUtils):Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $username = $authenticationUtils->getLastUsername();

        return $this->render('login/login.html.twig', [
            'username' => $username,
            'error' => $error !== null,
        ]);
    }

    //Envoi de mail réinitialisation de mot de passe 
    #[Route('/reset-password', name: 'app_reset_password', methods: ['GET', 'POST'])]
    public function resetPassword(Request $request, MailerInterface $mailer, UserRepository $userRepository,EntityManagerInterface $entityManager): Response
{
    $form = $this->createForm(SendEmailResetType::class);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $user = $userRepository->findOneBy(['email' => $form->get('email')->getData()]);

        if (!$user) {
            // Afficher un message d'erreur approprié
            return $this->render('login/reset-password.html.twig', ['form' => $form->createView(), 'error' => 'Aucun utilisateur trouvé avec cette adresse e-mail.']);
        }

        // Générer un jeton de réinitialisation de mot de passe
        $resetToken = (string) Ulid::generate();
        $user->setResetToken($resetToken);

        // Enregistrer l'utilisateur avec le nouveau jeton de réinitialisation de mot de passe
        $entityManager->persist($user);
        $entityManager->flush();

        $email = (new TemplatedEmail())
            ->from('gauthier.minor@gmail.com')
            ->to($form->get('email')->getData())
            ->subject('Réinitialisation de votre mot de passe')
            ->htmlTemplate('email/reset_password.html.twig')
            ->context([
                'user' => $user
            ]);

        $mailer->send($email);

        // Rediriger vers une page de confirmation ou une autre page appropriée
        return $this->redirectToRoute('app_login');
    }

    return $this->render('login/reset-password.html.twig', [
        'form' => $form->createView(),
    ]);
}

//réinitialisation de mot de passe
#[Route('/reset-password/{token}', name: 'app_reset_password_form', methods: ['GET', 'POST'])]
public function resetPasswordForm(Request $request, string $token, UserPasswordHasherInterface $passwordEncoder, UserRepository $userRepository,EntityManagerInterface $entityManager): Response
{
    // Rechercher l'utilisateur avec le jeton de réinitialisation de mot de passe
    $user = $userRepository->findOneBy(['resetToken' => $token]);

    if (!$user) {
        // Afficher un message d'erreur si le jeton est invalide ou a expiré
        return $this->render('login/reset_password_form.html.twig', ['error' => 'Jeton de réinitialisation de mot de passe invalide ou expiré.']);
    }

    $form = $this->createForm(ResetPasswordType::class);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Récupérer le nouveau mot de passe du formulaire
        $newPassword = $form->get('newPassword')->getData();

        // Encoder le nouveau mot de passe
        $encodedPassword = $passwordEncoder->hashPassword($user, $newPassword);


        // Mettre à jour le mot de passe de l'utilisateur dans la base de données
        $user->setPassword($encodedPassword);
        $user->setResetToken(null); // Réinitialiser le jeton de réinitialisation de mot de passe
        $entityManager->persist($user);
        $entityManager->flush();

        // Rediriger vers la page de connexion ou une autre page appropriée
        return $this->redirectToRoute('app_login');
    }

    return $this->render('login/reset_password_form.html.twig', ['form' => $form->createView()]);
}



    /**
     * Permet à l'utilisateur de se déconnecter
     *
     * @return void
     */
    #[Route("/logout", name:"app_logout")]
    public function logout(): void
    {
        // ..
    }
}
