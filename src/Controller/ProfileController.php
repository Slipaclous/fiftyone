<?php

// src/Controller/ProfileController.php
namespace App\Controller;

use App\Entity\User;
use App\Entity\Images;
use App\Form\UserType;
use App\Form\ImgModifyType;
use App\Entity\UserImgModify;
use App\Form\SetPasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ProfileController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route("/profile", name:"profile")]
    public function index(): Response
    {
        return $this->render('profile/index.html.twig', [
            'user' => $this->getUser(),
        ]);
    }

    /**
     * Modification du profil
     *
     * @param Request $request
     * @return Response
     */
    #[Route("/profile/edit", name:"edit")]
    public function editAction(Request $request): Response
    {
        $user = $this->getUser();

        // Créer un formulaire pour modifier le profil de l'utilisateur
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->addFlash('success', 'Profil mis à jour avec succès.');

        // Rediriger si le formulaire est soumis
        if ($form->isSubmitted()) {
            return $this->redirectToRoute('profile');
        }

        return $this->render('profile/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Modification du mot de passe
     *
     * @param Request $request
     * @param UserPasswordHasherInterface $passwordEncoder
     * @return Response
     */
#[Route("/set-password/{token}", name:"set_password_route")]
// Définit le nouveau mot de passe de l'utilisateur à l'aide du token de réinitialisation.
public function setPassword(Request $request, string $token, EntityManagerInterface $em, UserPasswordHasherInterface $passwordEncoder): Response
{
    // Recherche l'utilisateur par le token de réinitialisation du mot de passe.
    $user = $em->getRepository(User::class)->findOneBy(['passwordResetToken' => $token]);

    // Si l'utilisateur n'est pas trouvé ou que le token est invalide/expiré, renvoie une erreur.
    if (!$user) {
        throw $this->createNotFoundException('Token invalide ou expiré.');
    }

    // Crée et gère le formulaire de définition du nouveau mot de passe.
    $form = $this->createForm(SetPasswordType::class);
    $form->handleRequest($request);

    // Vérifie si le formulaire est soumis et valide.
    if ($form->isSubmitted() && $form->isValid()) {
        // Récupère les données du formulaire.
        $data = $form->getData();
        // Hash le nouveau mot de passe.
        $hashedPassword = $passwordEncoder->hashPassword($user, $data['plainPassword']);
        // Met à jour le mot de passe de l'utilisateur.
        $user->setPassword($hashedPassword);
        // Réinitialise le token de réinitialisation du mot de passe.
        $user->setPasswordResetToken(null); // Efface le token
        $user->setPasswordResetTokenExpiresAt(null); // Efface l'expiration du token
    
        // Persiste l'utilisateur et enregistre les changements dans la base de données.
        $em->persist($user);
        $em->flush();
    
        // Ajoute un message flash de succès.
        $this->addFlash('success', 'Mot de passe défini avec succès!');
    
        // Redirige vers la page de connexion.
        return $this->redirectToRoute('app_login'); // Redirige vers votre page de connexion
    }

    // Affiche le formulaire de définition du nouveau mot de passe.
    return $this->render('change_password/path_to_set_password_template.html.twig', [
        'form' => $form->createView(),
        'token' => $token,
    ]);
}

    /**
     * Modification de l'image de profil
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route("/account/imgmodify", name: "account_modifimg")]
    public function imgModify(Request $request, EntityManagerInterface $manager): Response
    {
        $imgModify = new UserImgModify();
        $user = $this->getUser();
        $form = $this->createForm(ImgModifyType::class, $imgModify);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Supprimer l'ancien fichier d'avatar s'il existe
            $oldAvatarUrl = $user->getAvatar();
            if ($oldAvatarUrl !== null) {
                $this->removeOldAvatar($oldAvatarUrl);
            }

            $file = $form['newPicture']->getData();
            if (!empty($file)) {
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = transliterator_transliterate('Any-Latin;Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
                $newFilename = $safeFilename . "-" . uniqid() . "." . $file->guessExtension();
                try {
                    $file->move(
                        $this->getParameter('uploads_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    return $e->getMessage();
                }

                // Définir la nouvelle URL comme avatar de l'utilisateur
                $user->setAvatar('images/' . $newFilename);
            } else {
                // Si aucun nouveau fichier n'est téléchargé, définir l'avatar sur null
                $user->setAvatar(null);
            }

            $manager->persist($user);
            $manager->flush();

            $this->addFlash(
                'success',
                'Votre avatar a bien été modifié'
            );

            return $this->redirectToRoute('profile');
        }

        return $this->render("profile/imgModify.html.twig", [
            'myform' => $form->createView()
        ]);
    }

    /**
     * Supprimer l'ancien avatar de l'utilisateur
     * @param string|null $avatarUrl The URL of the old avatar file
     */
    private function removeOldAvatar(?string $avatarUrl): void
    {
        if ($avatarUrl !== null) {
            $path = $this->getParameter('uploads_directory') . '/' . $avatarUrl;

            if (file_exists($path)) {
                unlink($path);
            }
        }
    }
}

