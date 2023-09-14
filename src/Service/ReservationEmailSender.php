<?php 
// src/Service/ReservationEmailSender.php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment as TwigEnvironment; // Import Twig\Environment

class ReservationEmailSender
{
    private MailerInterface $mailer;
    private TwigEnvironment $twig; // Inject Twig\Environment

    public function __construct(MailerInterface $mailer, TwigEnvironment $twig) // Inject Twig\Environment
    {
        $this->mailer = $mailer;
        $this->twig = $twig; // Store the Twig\Environment service
    }

    public function sendReservationConfirmationEmail($toEmail, $subject, $event, $reservation, $emailContent) // Add $emailContent parameter
    {
        $email = (new Email())
            ->from('gauthier.minor@gmail.com') // Replace with your Gmail email address
            ->to($toEmail)
            ->subject($subject)
            ->html($emailContent); // Use the provided email content

        $this->mailer->send($email);
    }
}
