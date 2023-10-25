<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use App\Entity\MemberEvents;
use Doctrine\ORM\EntityManagerInterface;



#[AsCommand(
    name: 'App:ListMemberEvents',
    description: 'Add a short description for your command',
)]
class ListMemberEventsCommand extends Command
{
    private EntityManagerInterface $entityManager;
    public function __construct(EntityManagerInterface $entityManager)
{
    parent::__construct();

    $this->entityManager = $entityManager;
}
protected function configure(): void
{
    $this
        // the short description shown while running "php bin/console list"
        ->setDescription('Lists all member events.')
        
        // the command's name (after "php bin/console")
        ->setName('app:list-member-events')
        
        // the full command description shown when running the command with
        // the "--help" option
        ->setHelp('This command allows you to list all member events...')
    ;
}

    protected function execute(InputInterface $input, OutputInterface $output,): int
{
    $repository = $this->entityManager->getRepository(MemberEvents::class);
    $events = $repository->findAll();

    if (!$events) {
        $output->writeln('<info>No events found</info>');
        return Command::SUCCESS;
    }

    foreach ($events as $event) {
        $output->writeln(sprintf('<info>%s:</info> %s (Date: %s, Places: %d)', $event->getId(), $event->getTitre(), $event->getDate()->format('Y-m-d'), $event->getPlaces()));
    }

    return Command::SUCCESS;
}
}
