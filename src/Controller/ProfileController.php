<?php

// src/Controller/ProfileController.php
namespace App\Controller;

use App\Entity\User;
use App\Entity\Images;
use App\Form\UserType;
use App\Form\ImgModifyType;
use App\Entity\UserImgModify;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

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
     #[Route("/profile/edit", name:"edit")]
     
     public function editAction(Request $request): Response
     {
         $user = $this->getUser();
     
         // Create a form to edit the user's profile
         $form = $this->createForm(UserType::class, $user);
         $form->handleRequest($request);
     
     
             $this->entityManager->persist($user);
             $this->entityManager->flush();
     
             $this->addFlash('success', 'Profile updated successfully.');
     
             // redirect if form issubmùitted
                if($form->isSubmitted()){
                    return $this->redirectToRoute('profile');
                }
     
            
         
     
         return $this->render('profile/edit.html.twig', [
             'form' => $form->createView(),
         ]);
     }
     

     #[Route("/account/imgmodify", name: "account_modifimg")]
public function imgModify(Request $request, EntityManagerInterface $manager): Response
{
    $imgModify = new UserImgModify();
    $user = $this->getUser();
    $form = $this->createForm(ImgModifyType::class, $imgModify);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Remove the old avatar file if it exists
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

            // Set the new URL as the user's avatar
            $user->setAvatar('images/' . $newFilename);
        } else {
            // If no new file is uploaded, set the avatar to null
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


