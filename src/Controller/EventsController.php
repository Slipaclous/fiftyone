<?php

namespace App\Controller;

use App\Entity\Events;
use App\Entity\Reservation;
use App\Form\ReservationType;
use App\Repository\EventsRepository;
use App\Service\ReservationEmailSender;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ReservationRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
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
        ]);
    }
    //route
    #[Route('/events/{slug}/reservation', name: 'app_events_reservation')]
    public function makeReservation(
        Request $request,
        Events $event,
        EntityManagerInterface $entityManager,
        ReservationEmailSender $emailSender,
    ): Response {
        $reservation = new Reservation();
        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Generate a unique reservation number (you can use a custom logic)
            $reservation->setReservationNumber(uniqid());

            // Link the reservation to the event
            $reservation->setEvent($event);

            // Persist the reservation to the database
            $entityManager->persist($reservation);
            $entityManager->flush();

            // Update event availability
            $event->decrementAvailability($reservation->getNumberOfPeople());

            // Send an email with reservation details and reservation number
            $subject = 'Reservation Confirmation';
            $toEmail = $reservation->getEmail(); // Use the email address from the reservation entity

            // Render the email content using the email template
            $emailContent = $this->renderView(
                'email/reservation.html.twig',
                [
                    'event' => $event,
                    'reservation' => $reservation,
                ]
            );

            try {
                $emailSender->sendReservationConfirmationEmail($toEmail, $subject, $event, $reservation, $emailContent); // Pass $emailContent as the fourth argument
            } catch (\Exception $e) {
                // Handle email sending errors here, e.g., log the error
                $this->addFlash('error', 'Failed to send the reservation confirmation email.');
            }

            $this->addFlash('success', 'Reservation successful!'); // Optional: Flash message
            return $this->redirectToRoute('app_events_show', ['slug' => $event->getSlug()]);
        }

        return $this->render('events/reservation.html.twig', [
            'form' => $form->createView(),
            'event' => $event,
        ]);
    }
    

// Controller: Edit Reservation

#[Route('/events/{slug}/reservation/edit/{reservationNumber}', name: 'app_events_reservation_edit')]
public function editReservation(Request $request, Events $event, string $reservationNumber): Response
{
    // Load the reservation from the database using $reservationNumber
    $reservation = $this->loadReservationByNumber($reservationNumber);

    if (!$reservation) {
        // Handle the case where the reservation with the provided number does not exist
        $this->addFlash('error', 'Reservation not found.');
        return $this->redirectToRoute('app_events_reservation_edit_input', [
            'slug' => $event->getSlug(),
        ]);
    }

    // Create a form for modifying the reservation
    $form = $this->createForm(ReservationModificationType::class, $reservation);

    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Update the reservation in the database
        $this->updateReservation($reservation);

        // Redirect to a success page or any other desired action
        $this->addFlash('success', 'Reservation updated successfully.');
        return $this->redirectToRoute('app_events_show', ['slug' => $event->getSlug()]);
    }

    return $this->render('reservation/edit_reservation.html.twig', [
        'event' => $event,
        'reservation' => $reservation,
        'form' => $form->createView(),
    ]);
}


#[Route('/events/{slug}/reservation/edit/input', name: 'app_events_reservation_edit_input')]
public function editReservationInput(Request $request, Events $event): Response
{
    if ($request->isMethod('POST')) {
        $reservationNumber = $request->request->get('reservationNumber');

        // Validate the reservation number
        if ($this->isValidReservationNumber($reservationNumber)) {
            return $this->redirectToRoute('app_events_reservation_edit', [
                'slug' => $event->getSlug(),
                'reservationNumber' => $reservationNumber,
            ]);
        } else {
            $this->addFlash('error', 'Invalid reservation number. Please try again.');
        }
    }

    return $this->render('reservation/edit_reservation_input.html.twig', [
        'event' => $event,
    ]);
}



// Helper methods for loading and updating reservations

private function loadReservationByNumber(EntityManagerInterface $entityManager, string $reservationNumber): ?Reservation
{
    // Create a query builder for your Reservation entity
    $queryBuilder = $entityManager->getRepository(Reservation::class)->createQueryBuilder('r');

    // Add a condition to select the reservation with the given reservation number
    $queryBuilder->where('r.reservationNumber = :reservationNumber')
                 ->setParameter('reservationNumber', $reservationNumber);

    // Execute the query
    $reservation = $queryBuilder->getQuery()->getOneOrNullResult();

    return $reservation;
}


private function isValidReservationNumber(ReservationRepository $reservationRepository, string $reservationNumber): bool
{
    // Implement your validation logic here
    $reservation = $reservationRepository->findOneBy(['reservationNumber' => $reservationNumber]);

    // Check if a reservation with the given number exists
    return $reservation !== null;
}
private function updateReservation(EntityManagerInterface $entityManager, Reservation $reservation): void
{
    // Persist the updated reservation
    $entityManager->persist($reservation);

    // Flush changes to the database
    $entityManager->flush();
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
}
