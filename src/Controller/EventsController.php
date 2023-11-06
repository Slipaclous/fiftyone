<?php

namespace App\Controller;

use App\Entity\Events;
use App\Entity\Reservation;
use App\Form\ReservationType;
use App\Form\ModifyReservationType;
use App\Repository\EventsRepository;
use App\Service\ReservationEmailSender;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ReservationRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class EventsController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/events', name: 'app_events')]
public function index(EventsRepository $eventsRepository, PaginatorInterface $paginator, Request $request): Response
{
    $searchQuery = $request->query->get('search');
    $closestEvents = $eventsRepository->findClosestEvents();

    // Crée le constructeur de requête pour récupérer les événements
    $eventsQueryBuilder = $eventsRepository->createQueryBuilder('e')
        ->orderBy('e.date', 'DESC');

    if ($searchQuery) {
        $eventsQueryBuilder->andWhere('e.titre LIKE :searchQuery OR e.description LIKE :searchQuery')
            ->setParameter('searchQuery', '%' . $searchQuery . '%');
    }

    $eventsQuery = $eventsQueryBuilder->getQuery();

    $pagination = $paginator->paginate(
        $eventsQuery,
        $request->query->getInt('page', 1), // Numéro de page actuelle, par défaut 1
        8 // Nombre d'éléments par page
    );
    
  

    return $this->render('events/index.html.twig', [
        'events' => $pagination,
        'closestEvents' => $closestEvents,
    ]);
}
    // Route pour effectuer une réservation pour un événement
#[Route('/events/{slug}/reservation', name: 'app_events_reservation')]
public function makeReservation(
    Request $request,Events $event,EntityManagerInterface $entityManager,ReservationEmailSender $emailSender): Response 
    {
    $reservation = new Reservation();
    $form = $this->createForm(ReservationType::class, $reservation);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Générer un numéro de réservation unique (vous pouvez utiliser une logique personnalisée)
        $reservation->setReservationNumber(uniqid());

        // Associer la réservation à l'événement
        $reservation->setEvent($event);

        // Persister la réservation dans la base de données
        $entityManager->persist($reservation);
        $entityManager->flush();

        // Mettre à jour la disponibilité de l'événement
        $event->decrementAvailability($reservation->getNumberOfPeople());

        // Envoyer un e-mail avec les détails de la réservation et le numéro de réservation
        $subject = 'Confirmation de réservation';
        $toEmail = $reservation->getEmail(); // Utiliser l'adresse e-mail de l'entité réservation

        // Générer le contenu de l'e-mail en utilisant le template d'e-mail
        $emailContent = $this->renderView(
            'email/reservation.html.twig',
            [
                'event' => $event,
                'reservation' => $reservation,
            ]
        );

        try {
            // Essayer d'envoyer l'e-mail de confirmation de réservation
            $emailSender->sendReservationConfirmationEmail($toEmail, $subject, $event, $reservation, $emailContent);
        } catch (\Exception $e) {
            // Gérer les erreurs d'envoi d'e-mail ici, par exemple, logger l'erreur
            $this->addFlash('error', "L'envoi de l'e-mail de confirmation de réservation a échoué.");
        }

        // Message flash optionnel pour indiquer le succès de l'action
        $this->addFlash('success', 'Réservation effectuée avec succès !');
        return $this->redirectToRoute('app_events_show', ['slug' => $event->getSlug()]);
    }

    return $this->render('events/reservation.html.twig', [
        'form' => $form->createView(),
        'event' => $event,
    ]);
}

// Route pour supprimer une réservation
#[Route('/reservation/{id}/delete', name: 'app_delete_reservation')]
public function deleteReservation(Reservation $reservation, EntityManagerInterface $entityManager): Response {
    $event = $reservation->getEvent();

    // Supprimer la réservation et mettre à jour la base de données
    $entityManager->remove($reservation);
    $entityManager->flush();

    // Message flash pour confirmer la suppression de la réservation
    $this->addFlash('success', 'Réservation supprimée avec succès.');

    return $this->redirectToRoute('app_events_show', ['slug' => $event->getSlug()]);
}

// Route pour rechercher une réservation afin de la modifier
#[Route('/events/reservation/modify', name: 'app_modify_reservation')]
public function modifyReservation(Request $request, EntityManagerInterface $entityManager): Response {
    $form = $this->createFormBuilder()
        ->add('reservationNumber', TextType::class, [
            'label' => 'Numéro de réservation',
            'attr' => ['placeholder' => 'Entrez votre numéro de réservation'],
        ])
        ->getForm();

    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Rechercher la réservation avec le numéro fourni
        $reservationNumber = $form->getData()['reservationNumber'];
        $reservation = $entityManager->getRepository(Reservation::class)->findOneBy(['reservationNumber' => $reservationNumber]);

        if ($reservation) {
            // Rediriger vers le formulaire de modification si la réservation est trouvée
            return $this->redirectToRoute('app_modify_reservation_form', ['id' => $reservation->getId()]);
        } else {
            // Message flash si aucune réservation n'est trouvée
            $this->addFlash('error', 'Aucune réservation trouvée pour ce numéro.');
        }
    }

    return $this->render('events/modify.html.twig', [
        'form' => $form->createView(),
    ]);
}
    
    // Route pour le formulaire de modification d'une réservation spécifique
#[Route('/reservation/{id}/modify', name: 'app_modify_reservation_form')]
public function modifyReservationForm(Request $request, Reservation $reservation, EntityManagerInterface $entityManager): Response {
    // Créer et gérer le formulaire de modification d'une réservation existante
    $form = $this->createForm(ModifyReservationType::class, $reservation);

    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Enregistrer les modifications de la réservation dans la base de données
        $entityManager->persist($reservation);
        $entityManager->flush();

        // Message flash pour indiquer le succès de la modification de la réservation
        $this->addFlash('success', 'La modification de votre réservation a été enregistrée avec succès.');

        // Redirection vers la page de détail de l'événement après modification de la réservation
        return $this->redirectToRoute('app_events_show', ['slug' => $reservation->getEvent()->getSlug()]);
    }

    // Afficher le formulaire de modification si la requête n'est pas valide ou pas encore soumise
    return $this->render('events/modify_form.html.twig', [
        'form' => $form->createView(),
        'reservation' => $reservation,
    ]);
}
    
    /**
     * Affiche un événement
     *
     * @param string $slug
     * @param EventsRepository $eventsRepository
     * @return Response
     */
    #[Route('/events/{slug}', name: 'app_events_show')]
    public function show(string $slug, EventsRepository $eventsRepository): Response
    {
        $event = $eventsRepository->findOneBy(['slug' => $slug]);

        if (!$event) {
            throw $this->createNotFoundException('Événement introuvable');
        }

        return $this->render('events/show.html.twig', [
            'event' => $event,
        ]);
    }
    #[Route('/admin/events/{id}/print-reservations', name: 'admin_print_reservations')]
public function printReservations(Events $event): Response
{
    $reservations = $event->getReservations();

    return $this->render('admin/events/print_reservations.html.twig', [
        'event' => $event,
        'reservations' => $reservations,
    ]);
}
}
