<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\MemberEvents;
use App\Entity\User;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
    name: 'App:SendEventsEmailCommand',
    description: 'Add a short description for your command',
)]
class SendEventsEmailCommand extends Command
{
    protected static $defaultName = 'app:sendEventsEmail';

    private $entityManager;
    private $mailer;

    public function __construct(EntityManagerInterface $entityManager, MailerInterface $mailer)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->mailer = $mailer;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Assume you have a field 'createdAt' in your MemberEvents entity
        // and a method to fetch all events created after a certain date.
        $lastWednesday = new \DateTime('last Wednesday');
        $newEvents = $this->entityManager->getRepository(MemberEvents::class)->findNewEventsSinceLastWednesday();

        if (count($newEvents) > 0) {
            $users = $this->entityManager->getRepository(User::class)->findAll();

            foreach ($users as $user) {
                $email = (new Email())
                    ->from('gauthier.minor@gmail.com')
                    ->to($user->getEmail())
                    ->subject('Nouveaux évènements de membres!')
                    ->html($this->renderEventsEmail($newEvents));

                $this->mailer->send($email);
            }

            $output->writeln('Emails have been sent!');
        } else {
            $output->writeln('No new events to send.');
        }

        return Command::SUCCESS;
    }

    private function renderEventsEmail($events): string
    {
        // Render the email content based on the new events.
        // This could be a Twig template, or simply a concatenation of strings.
        // For example:
        $content = '<h1>New Member Events!</h1>';
        foreach ($events as $event) {
            $content .= '<p>' . $event->getTitre() . ' on ' . $event->getDate()->format('Y-m-d') . '</p>';
        }

        return $content;
    }
}
