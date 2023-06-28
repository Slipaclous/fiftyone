<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Form\ContactType;
use Symfony\Component\Mime\Email;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;

class ContactController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/contact", name="contact", methods={"GET", "POST"})
     */
    public function showContactForm(Request $request, MailerInterface $mailer): Response
    {
        $contact = new Contact();
        $form = $this->createForm(ContactType::class, $contact);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Envoyer un e-mail à l'administrateur
            $email = (new Email())
                ->from($contact->getEmail())
                ->to('gauthier.minor@gmail.com')
                ->subject('Nouveau message de contact')
                ->text($contact->getMessage());

            $mailer->send($email);

            // Sauvegarder le message de contact et l'e-mail dans la base de données
            $this->entityManager->persist($contact);
            $this->entityManager->flush();

            // Rediriger vers une page de succès ou afficher un message de succès
            return $this->redirectToRoute('contact_success');
        }

        return $this->render('contact/contact.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/contact/success", name="contact_success")
     */
    public function contactSuccess(): Response
    {
        $this->addFlash('success', 'Votre message a été envoyé avec succès.');

        return $this->redirectToRoute('app_home');
    }
}
