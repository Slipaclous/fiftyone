<?php

// Définition de l'espace de noms pour la commande
namespace App\Command;

// Importation des classes nécessaires de Symfony et de votre application
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Entity\MemberEvents;
use Doctrine\ORM\EntityManagerInterface;

// Utilisation de l'attribut #[AsCommand] pour définir les informations de la commande
#[AsCommand(
    name: 'App:ListMemberEvents',
    description: 'Ajoute une courte description pour votre commande',
)]
class ListMemberEventsCommand extends Command
{
    // Déclaration de la propriété pour le gestionnaire d'entités
    private EntityManagerInterface $entityManager;

    // Constructeur avec injection de l'EntityManager
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    // Configuration de la commande
    protected function configure(): void
    {
        $this
            // Courte description affichée lors de l'exécution de "php bin/console list"
            ->setDescription('Liste tous les événements des membres.')
            
            // Nom de la commande (après "php bin/console")
            ->setName('app:list-member-events')
            
            // Description complète affichée lors de l'exécution de la commande avec l'option "--help"
            ->setHelp('Cette commande vous permet de lister tous les événements des membres...');
    }

    // La méthode execute est le point d'entrée de la commande lorsqu'elle est exécutée
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Récupération du dépôt des événements des membres
        $repository = $this->entityManager->getRepository(MemberEvents::class);
        $events = $repository->findAll();

        // Vérifie si aucun événement n'est trouvé
        if (!$events) {
            $output->writeln('<info>Aucun événement trouvé</info>');
            return Command::SUCCESS;
        }

        // Affichage des informations de chaque événement trouvé
        foreach ($events as $event) {
            $output->writeln(sprintf('<info>%s:</info> %s (Date : %s, Places : %d)', $event->getId(), $event->getTitre(), $event->getDate()->format('Y-m-d'), $event->getPlaces()));
        }

        // Retourne le statut de succès de la commande
        return Command::SUCCESS;
    }
}
