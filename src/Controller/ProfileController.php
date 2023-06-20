<?php

// src/Controller/ProfileController.php
namespace App\Controller;

use App\Entity\User;
use App\Entity\Images;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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
         $originalAvatar = $user->getAvatar(); // Keep the original avatar
     
         // Create a form to edit the user's profile
         $form = $this->createForm(UserType::class, $user);
         $form->handleRequest($request);
     
         if ($form->isSubmitted() && $form->isValid()) {
             $avatarFile = $form->get('avatar')->getData();
     
             // Process the uploaded avatar file
             if ($avatarFile instanceof UploadedFile) {
                 $newFilename = md5(uniqid()) . '.' . $avatarFile->guessExtension();
                 $avatarFile->move($this->getParameter('avatar_directory'), $newFilename);
     
                 // Create a new Images entity and set its URL
                 $image = new Images();
                 $image->setUrl('images/' . $newFilename);
                 $this->entityManager->persist($image);
                 $this->entityManager->flush();
     
                 // Set the new Images entity as the user's avatar
                 $user->setAvatar($image);
             }
     
             $this->entityManager->persist($user);
             $this->entityManager->flush();
     
             $this->addFlash('success', 'Profile updated successfully.');
     
             // Remove the original avatar file if a new one is uploaded
             if ($avatarFile instanceof UploadedFile && $originalAvatar !== null) {
                 $this->removeOldAvatar($originalAvatar);
             }
     
             return $this->redirectToRoute('app_home');
         }
     
         return $this->render('profile/edit.html.twig', [
             'form' => $form->createView(),
         ]);
     }
     
     private function removeOldAvatar(Images $avatar): void
     {
         $path = $this->getParameter('public_directory') . '/' . $avatar->getUrl();
     
         if (file_exists($path)) {
             unlink($path);
         }
     
         $this->entityManager->remove($avatar);
         $this->entityManager->flush();
     }
     
}


