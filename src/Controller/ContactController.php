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
            // Send email to the administrator
            $email = (new Email())
                ->from($contact->getEmail())
                ->to('gauthier.minor@gmail.com')
                ->subject('New Contact Message')
                ->text($contact->getMessage());

            $mailer->send($email);

            // Save the contact message and email to the database
            $this->entityManager->persist($contact);
            $this->entityManager->flush();

            // Redirect to a success page or display a success message
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
        $this->addFlash('success', 'Your message has been sent successfully.');

        return $this->redirectToRoute('app_home');
    }
}
