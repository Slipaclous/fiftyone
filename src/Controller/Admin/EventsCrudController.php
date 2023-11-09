<?php

namespace App\Controller\Admin;

use App\Entity\Events;
use App\Form\Type\ImageUploadType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

// Ce contrôleur étend le contrôleur CRUD d'EasyAdminBundle pour gérer les 'Events' (événements)
class EventsCrudController extends AbstractCrudController
{   
    public static function getEntityFqcn(): string
    {
        // Retourne le nom complet de la classe de l'entité que ce CRUD contrôleur gère
        return Events::class;
    }

    // Définition de la route pour le listing des événements dans l'admin
    #[Route('/admin/events', name: 'admin_events')]
    // Configuration des champs à afficher dans le CRUD
    public function configureFields(string $pageName): iterable
    {
        // Champs à afficher, en utilisant des 'yield' pour les retourner un par un
        yield IdField::new('id')->hideOnForm(); // Champ 'id', caché dans le formulaire
        yield TextField::new('titre'); // Champ pour le titre de l'événement
        yield ImageField::new('cover')
            ->setBasePath('/images') // Chemin de base pour les images
            ->setUploadDir('public/images') // Dossier de téléchargement des images
            ->setUploadedFileNamePattern('[randomhash].[extension]') // Modèle de nommage des fichiers téléchargés
            ->setRequired(false); // L'upload n'est pas obligatoire
        yield TextEditorField::new('description'); // Éditeur de texte pour la description
        yield DateField::new('date'); // Sélecteur de date
        yield IntegerField::new('places'); // Champ pour le nombre de places

        // Champ de collection pour télécharger plusieurs images
        
    }

    public function configureActions(Actions $actions): Actions
    {
        // Creating a new custom action for printing reservations only if places are not null and not zero
        $printReservations = Action::new('printReservations', 'Imprimer Réservations', 'fa fa-print')
            ->displayIf(function (Events $event) {
                return $event->getPlaces() !== null && $event->getPlaces() > 0; // The print button is shown only if there are places available
            })
            ->linkToRoute('admin_print_reservations', function (Events $event): array {
                return ['id' => $event->getId()];
            });
    
        // Add the new action to the default actions
        return parent::configureActions($actions)
            ->add(Crud::PAGE_INDEX, $printReservations) // Adding it to the index page
            ->add(Crud::PAGE_INDEX, Action::DETAIL); // Adding the 'DETAIL' action on the index page
    }
    

    // Redéfinition de la méthode index pour ajouter des contrôles d'accès ou modifier le comportement par défaut
    public function index(AdminContext $context)
    {
        // Vérification des droits de l'utilisateur
        if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_MEMBER')) {
            // Redirection vers la page d'erreur 403 si l'utilisateur n'a pas les droits nécessaires
            return new Response($this->renderView('bundles/TwigBundle/Exception/error403.html.twig'), Response::HTTP_FORBIDDEN);
        }
        
        // Appel de la méthode parente pour le comportement par défaut de listing
        $response = parent::index($context);

        return $response;
    }

    // Méthode pour afficher les détails d'un événement spécifique
    public function detail(AdminContext $context)
    {
        // Récupération de l'entité 'event' actuelle
        $event = $context->getEntity()->getInstance();
        // Récupération des réservations liées à cet événement
        $reservations = $event->getReservations();

        // Rendu de la vue détaillée avec l'événement et ses réservations
        return $this->render('admin/events/detail.html.twig', [
            'event' => $event,
            'reservations' => $reservations,
        ]);
    }
}
