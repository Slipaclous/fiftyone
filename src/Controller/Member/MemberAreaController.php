<?php

namespace App\Controller\Member;

use App\Entity\Metting;
use App\Form\MeetingType;
use App\Repository\MettingRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\MemberEventsRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MemberAreaController extends AbstractController
{
    #[Route('/member/area', name: 'member_area')]
    #[IsGranted('ROLE_USER')]
    public function index(MemberEventsRepository $eventsRepository,MettingRepository $meetingRepository): Response
    {
        // Get the next event
        $nextEvent = $eventsRepository->findNextEvent();
        $nextMeeting = $meetingRepository->findNextMeeting();

        return $this->render('member/index.html.twig', [
            'controller_name' => 'MemberAreaController',
            'event' => $nextEvent,
            'meeting' => $nextMeeting,
        ]);
    }

    #[Route('/upcoming-meetings', name: 'upcoming_meetings')]
    public function upcomingMeetings(MettingRepository $meetingRepository): Response
    {
        $upcomingMeetings = $meetingRepository->findUpcomingMeetingsGroupedByMonth();
    
        return $this->render('member/upcoming_meetings.html.twig', [
            'upcoming_meetings' => $upcomingMeetings,
        ]);
    }

#[Route('/create-meeting', name: 'create_meeting')]
public function createMeetingForm(Request $request, EntityManagerInterface $entityManager): Response
{
    $meeting = new Metting();
    $form = $this->createForm(MeetingType::class, $meeting);

    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) {
        // Handle the form submission and persist the meeting
        $entityManager->persist($meeting);
        $entityManager->flush();

        $this->addFlash('success', 'Meeting created successfully.');

        // Redirect to the page showing all upcoming meetings
        return $this->redirectToRoute('upcoming_meetings');
    }

    return $this->render('member/create_meeting.html.twig', [
        'form' => $form->createView(),
    ]);
}

}
