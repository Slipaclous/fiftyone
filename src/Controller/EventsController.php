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
    
    // Use dump here
    foreach ($pagination as $event) {
        dump($event->getPlaces());
    }

    return $this->render('events/index.html.twig', [
        'events' => $pagination,
        'closestEvents' => $closestEvents,
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


    #[Route('/events/modify', name: 'app_modify_reservation')]
    public function modifyReservation(Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createFormBuilder()
            ->add('reservationNumber', TextType::class, [
                'label' => 'Reservation Number',
                'attr' => ['placeholder' => 'Enter your reservation number'],
            ])
            ->getForm();
    
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $reservationNumber = $form->getData()['reservationNumber'];
    
            $reservation = $entityManager->getRepository(Reservation::class)->findOneBy(['reservationNumber' => $reservationNumber]);
    
            if ($reservation) {
                return $this->redirectToRoute('app_modify_reservation_form', ['id' => $reservation->getId()]);
            } else {
                $this->addFlash('error', 'No reservation found with the provided reservation number.');
            }
        }
    
        return $this->render('events/modify.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    
    #[Route('/reservation/{id}/modify', name: 'app_modify_reservation_form')]
    public function modifyReservationForm(Request $request, Reservation $reservation, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ModifyReservationType::class, $reservation);
    
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($reservation);
            $entityManager->flush();
    
            $this->addFlash('success', 'Reservation updated successfully.');
    
            return $this->redirectToRoute('app_events_show', ['slug' => $reservation->getEvent()->getSlug()]);
        }
    
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
}
