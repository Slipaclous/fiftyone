<?php

namespace App\Controller\Member;

use App\Entity\Metting;
use App\Form\MeetingType;
use App\Entity\MeetingSummary;
use App\Form\MeetingSummaryType;
use App\Repository\MettingRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\MemberEventsRepository;
use App\Repository\MeetingSummaryRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
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
#[Route('/create-summary', name:'create_summary')]
public function createMeetingSummary(Request $request, EntityManagerInterface $entityManager): Response
    {
        $summary = new MeetingSummary();
        $form = $this->createForm(MeetingSummaryType::class, $summary);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle file upload (PDF)
            $pdfFile = $form->get('pdf')->getData();
            if ($pdfFile) {
                $pdfFileName = uniqid().'.'.$pdfFile->guessExtension();
                $pdfFile->move(
                    $this->getParameter('uploads_directory'),
                    $pdfFileName
                );
                $summary->setPdf($pdfFileName);
            }

            // Set the last meeting date
            $summary->setDate($form->get('date')->getData());

            // Save to the database
            $entityManager->persist($summary);
            $entityManager->flush();

            return $this->redirectToRoute('member_area', ['id' => $summary->getId()]);
        }

        return $this->render('member/create_summary.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route("/summary-list", name:"summary_list")]
    public function showSummaryList(MeetingSummaryRepository $summaryRepository): Response
    {
        $summaries = $summaryRepository->findAll();

        return $this->render('member/list_summary.html.twig', [
            'summaries' => $summaries,
        ]);
    }

     
    #[Route("/pdf-display/{pdf}", name:"pdf_display")]
public function displayPdf(string $pdf): Response
{
    $pdfPath = $this->getParameter('uploads_directory').'/'.$pdf;

    // Check if the file exists
    if (!file_exists($pdfPath)) {
        throw $this->createNotFoundException('The PDF file does not exist.');
    }

    // Create a BinaryFileResponse
    $response = new BinaryFileResponse($pdfPath);
    $response->headers->set('Content-Type', 'application/pdf');
    $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_INLINE);

    return $response;
}

}
