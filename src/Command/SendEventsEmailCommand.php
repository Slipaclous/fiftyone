<?php

namespace App\Command;

// Importations des classes nécessaires à la commande
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\MemberEvents;
use App\Entity\User;
use Symfony\Component\Console\Attribute\AsCommand;

// Annotation pour configurer la commande CLI
#[AsCommand(
    name: 'App:SendEventsEmailCommand',
    description: 'Ajoute une courte description pour votre commande',
)]
class SendEventsEmailCommand extends Command
{
    // Nom par défaut de la commande
    protected static $defaultName = 'app:sendEventsEmail';

    // Déclaration des propriétés pour le gestionnaire d'entité et le service de messagerie
    private $entityManager;
    private $mailer;

    // Constructeur avec injection de dépendances pour EntityManager et MailerInterface
    public function __construct(EntityManagerInterface $entityManager, MailerInterface $mailer)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->mailer = $mailer;
    }

    // La méthode execute est le point d'entrée de la commande lorsqu'elle est exécutée
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $lastWednesday = new \DateTime('last Wednesday');
        $newEvents = $this->entityManager->getRepository(MemberEvents::class)->findNewEventsSinceLastWednesday();

        // Vérifier s'il y a de nouveaux événements
        if (count($newEvents) > 0) {
            // Récupérer tous les utilisateurs
            $users = $this->entityManager->getRepository(User::class)->findAll();

            // Envoyer un email à chaque utilisateur
            foreach ($users as $user) {
                $email = (new Email())
                    ->from('gauthier.minor@gmail.com')
                    ->to($user->getEmail())
                    ->subject('Nouveaux évènements de membres!')
                    ->html($this->renderEventsEmail($newEvents)); // Générer le contenu de l'email

                $this->mailer->send($email);
            }

            // Message de confirmation d'envoi des emails
            $output->writeln('Les emails ont été envoyés !');
        } else {
            // Message s'il n'y a pas de nouveaux événements
            $output->writeln('Pas de nouveaux événements à envoyer.');
        }

        // Retourner le statut de succès de la commande
        return Command::SUCCESS;
    }

    // Méthode privée pour rendre le contenu de l'email basé sur les nouveaux événements
    private function renderEventsEmail($events): string
    {
        // Générer le contenu de l'email.
        // Cela pourrait être un template Twig, ou simplement une concaténation de chaînes.
        // Par exemple :
        $content = '<h1>Nouveaux événements des membres !</h1>';
        foreach ($events as $event) {
            $content .= '<p>' . $event->getTitre() . ' le ' . $event->getDate()->format('Y-m-d') . '</p>';
        }

        return $content;
    }
}
