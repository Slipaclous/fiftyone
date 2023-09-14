<?php

namespace App\Controller\Member;

use App\Entity\Guests;
use App\Form\GuestListType;
use App\Entity\MemberEvents;
use App\Form\MemberEventType;
use App\Entity\EventParticipant;
use App\Form\EventParticipantType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use App\Repository\MemberEventsRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use App\Repository\EventParticipantRepository;
use Symfony\Component\HttpFoundation\Response;
use Vich\UploaderBundle\Handler\UploadHandler;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class MemberEventController extends AbstractController
{
    #[Route('/member-events', name: 'app_member_event')]
    public function index(MemberEventsRepository $eventRepository, EventParticipantRepository $participantRepository, Security $security): Response
    {
        $user = $security->getUser();
        $events = $eventRepository->findAll();

        $participationStatus = [];

        if ($user) {
            foreach ($events as $event) {
                $participationStatus[$event->getId()] = $participantRepository->hasUserParticipated($user, $event);
            }
        }

        return $this->render('member-event/index.html.twig', [
            'events' => $events,
            'user' => $user,
            'participationStatus' => $participationStatus,
        ]);
    }
    #[Route('/member-event/{id}', name: 'app_member_event_details')]
public function showEventDetails(MemberEvents $event, EventParticipantRepository $participantRepository, Request $request, EntityManagerInterface $entityManager): Response
{
    $participants = $participantRepository->findParticipantsForEvent($event);
    $participant = $participantRepository->findOneBy(['event' => $event, 'participant' => $this->getUser()]);

    $participantForm = $this->createParticipantForm($event);

    $participantForm->handleRequest($request);
    if ($participantForm->isSubmitted() && $participantForm->isValid()) {
        $this->processParticipantForm($participantForm, $event, $entityManager);

        $this->addFlash('success', 'You have successfully participated in the event.');

        return $this->redirectToRoute('app_member_event_details', ['id' => $event->getId()]);
    }

    return $this->render('member-event/show-details.html.twig', [
        'event' => $event,
        'participants' => $participants,
        'participant' => $participant,
        'participantForm' => $participantForm->createView(),
    ]);
}
#[Route('/edit-guest-list/{id}', name: 'app_edit_guest_list')]
public function editGuestList(EventParticipant $eventParticipant, Request $request, EntityManagerInterface $entityManager): Response
{
    // Check if the current user has permission to edit this guest list
    if ($this->getUser() !== $eventParticipant->getParticipant()) {
        throw new AccessDeniedException('You do not have permission to edit this guest list.');
    }

    $form = $this->createForm(GuestListType::class, $eventParticipant);

    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) {
        // Get the submitted data
        $data = $form->getData();

        // Handle the form submission and persist the data.
        $entityManager->flush(); // Flush all changes to the database

        $this->addFlash('success', 'Your guest list has been updated.');

        return $this->redirectToRoute('app_member_event_details', ['id' => $eventParticipant->getEvent()->getId()]);
    }

    return $this->render('member-event/edit-guest-list.html.twig', [
        'eventParticipant' => $eventParticipant,
        'form' => $form->createView(),
    ]);
}

//Supression d'un guest
#[Route('/delete-guest/{id}', name: 'app_delete_guest')]
public function deleteGuest(Guests $guest, EntityManagerInterface $entityManager): Response
{
    // Check if the current user has permission to delete this guest
    if ($this->getUser() !== $guest->getEventParticipant()->getParticipant()) {
        throw new AccessDeniedException('You do not have permission to delete this guest.');
    }

    $entityManager->remove($guest);
    $entityManager->flush();

    $this->addFlash('success', 'Guest deleted successfully.');

    return $this->redirectToRoute('app_edit_guest_list', ['id' => $guest->getEventParticipant()->getId()]);
}
    #[Route('/participate/{id}', name: 'app_participate')]
    public function participate(MemberEvents $event, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Create a new EventParticipant
        $participant = new EventParticipant();
        $participant->setEvent($event);
    
        // Retrieve the Participant entity, adjust this to your application's logic
        // Assuming $this->getUser() returns a User instance
        $participantEntity = $this->getUser();
    
        // Set the Participant entity for the EventParticipant
        $participant->setParticipant($participantEntity);
    
        // Create a form for the EventParticipant
        $form = $this->createForm(EventParticipantType::class, $participant);
    
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Clear any previous guests associated with this participant
            $participant->getGuests()->clear();
    
            // Add guests from the submitted form
            foreach ($form->get('guests') as $guestForm) {
                $guest = new Guests();
                $guest->setPrenom($guestForm->get('prenom')->getData());
                $guest->setNom($guestForm->get('nom')->getData());
                $guest->setEventParticipant($participant); // Set the association
                $entityManager->persist($guest);
            }
    
            // Handle the form submission and persist the data
            $entityManager->persist($participant);
            $entityManager->flush();
    
            $this->addFlash('success', 'You have successfully participated in the event.');
    
            return $this->redirectToRoute('app_member_event_details', ['id' => $event->getId()]);
        }
    
        return $this->render('member-event/participate.html.twig', [
            'event' => $event,
            'participantForm' => $form->createView(),
        ]);
    }



    private function createParticipantForm(MemberEvents $event): FormInterface
    {
        $participant = new EventParticipant();
        $participant->setParticipant($this->getUser());
        $participant->setEvent($event);

        return $this->createForm(EventParticipantType::class, $participant);
    }

    private function processParticipantForm(FormInterface $form, MemberEvents $event, EntityManagerInterface $entityManager): void
    {
        $data = $form->getData();

        $entityManager->persist($data);
        $entityManager->flush();
    }
  
    #[Route('/cancel-participation/{id}', name: 'app_cancel_participation')]
public function cancelParticipation(EventParticipant $eventParticipant, EntityManagerInterface $entityManager): Response
{
    // Check if the current user has permission to cancel their participation
    if ($this->getUser() !== $eventParticipant->getParticipant()) {
        throw new AccessDeniedException('You do not have permission to cancel your participation.');
    }

    // Remove the EventParticipant and associated guests
    $entityManager->remove($eventParticipant);
    $entityManager->flush();

    $this->addFlash('success', 'Participation canceled successfully.');

    return $this->redirectToRoute('app_member_event');
}
#[Route('/member/create-event', name: 'create_event')]
public function createEventForm(Request $request, EntityManagerInterface $entityManager, UploadHandler $uploadHandler): Response
{
    $event = new MemberEvents();
    $form = $this->createForm(MemberEventType::class, $event);

    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) {
        // Handle the event cover image upload
        if ($event->getCover()) {
            $uploadHandler->upload($event->getCover(), 'imageFile'); // Handle the upload for the associated image
        }

        $entityManager->persist($event);
        $entityManager->flush();

        $this->addFlash('success', 'Event created successfully!');

        // Redirect to the event list page with a success message
        return $this->redirectToRoute('app_member_event');
    }

    return $this->render('member-event/create-event.html.twig', [
        'event_form' => $form->createView(),
    ]);
}
}

