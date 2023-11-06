<?php

namespace App\Controller\Member;

// Importation des entités et des formulaires
use App\Entity\Metting;
use App\Form\MeetingType;
use App\Entity\MeetingSummary;
use App\Form\MeetingSummaryType;
use App\Repository\MessageRepository;
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
    // Action pour afficher la page d'accueil de l'espace membre
    #[Route('/member/area', name: 'member_area')]
    #[IsGranted('ROLE_USER')]
    public function index(MemberEventsRepository $eventsRepository, MeetingSummaryRepository $meetingSummaryRepository, MettingRepository $meetingRepository, MessageRepository $messageRepository): Response
    {
        if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_MEMBER')) {
            // Redirect the user to the 403 page
            return new Response($this->renderView('bundles/TwigBundle/Exception/error403.html.twig'), Response::HTTP_FORBIDDEN);
        }

        $user = $this->getUser();
        $unreadMessagesCount = $messageRepository->countUnreadMessagesForUser($user);

        // Récupérer le prochain événement
        $nextEvent = $eventsRepository->findNextEvent();
        
        // Récupérer la prochaine réunion
        $nextMeeting = $meetingRepository->findNextMeeting();
        
        // Récupérer le résumé de réunion le plus récent
        $mostRecentMeetingSummary = $meetingSummaryRepository->findMostRecentMeetingSummary();

        // Afficher la page d'accueil avec les données récupérées
        return $this->render('member/index.html.twig', [
            'controller_name' => 'MemberAreaController',
            'event' => $nextEvent,
            'meeting' => $nextMeeting,
            'most_recent_meeting_summary' => $mostRecentMeetingSummary,
            'unread_messages' => $unreadMessagesCount,
        ]);
    }

    // Action pour afficher les réunions à venir
    #[Route('/upcoming-meetings', name: 'upcoming_meetings')]
    public function upcomingMeetings(MettingRepository $meetingRepository): Response
    {
        // Récupérer les réunions à venir groupées par mois
        $upcomingMeetings = $meetingRepository->findUpcomingMeetingsGroupedByMonth();
    
        // Afficher la page des réunions à venir
        return $this->render('member/upcoming_meetings.html.twig', [
            'upcoming_meetings' => $upcomingMeetings,
        ]);
    }

    // Action pour afficher le formulaire de création de réunion
    #[Route('/create-meeting', name: 'create_meeting')]
    public function createMeetingForm(Request $request, EntityManagerInterface $entityManager): Response
    {
        $meeting = new Metting();
        $form = $this->createForm(MeetingType::class, $meeting);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Gérer la soumission du formulaire et persister la réunion
            $entityManager->persist($meeting);
            $entityManager->flush();

            $this->addFlash('success', 'Réunion créée avec succès.');

            // Rediriger vers la page affichant toutes les réunions à venir
            return $this->redirectToRoute('upcoming_meetings');
        }

        return $this->render('member/create_meeting.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // Action pour créer un résumé de réunion
    #[Route('/create-summary', name:'create_summary')]
    public function createMeetingSummary(Request $request, EntityManagerInterface $entityManager): Response
    {
        $summary = new MeetingSummary();
        $form = $this->createForm(MeetingSummaryType::class, $summary);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gérer le téléchargement du fichier (PDF)
            $pdfFile = $form->get('pdf')->getData();
            if ($pdfFile) {
                $pdfFileName = uniqid().'.'.$pdfFile->guessExtension();
                $pdfFile->move(
                    $this->getParameter('uploads_directory'),
                    $pdfFileName
                );
                $summary->setPdf($pdfFileName);
            }

            // Définir la date de la dernière réunion
            $summary->setDate($form->get('date')->getData());

            // Enregistrer dans la base de données
            $entityManager->persist($summary);
            $entityManager->flush();

            return $this->redirectToRoute('member_area', ['id' => $summary->getId()]);
        }

        return $this->render('member/create_summary.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // Action pour afficher la liste des résumés de réunion
    #[Route("/summary-list", name:"summary_list")]
    public function showSummaryList(MeetingSummaryRepository $summaryRepository): Response
    {
        $summaries = $summaryRepository->findAll();

        return $this->render('member/list_summary.html.twig', [
            'summaries' => $summaries,
        ]);
    }

    // Action pour afficher un fichier PDF
    #[Route("/pdf-display/{pdf}", name:"pdf_display")]
    public function displayPdf(string $pdf): Response
    {
        $pdfPath = $this->getParameter('uploads_directory').'/'.$pdf;

        // Vérifier si le fichier existe
        if (!file_exists($pdfPath)) {
            throw $this->createNotFoundException('Le fichier PDF n\'existe pas.');
        }

        // Créer une réponse de fichier binaire
        $response = new BinaryFileResponse($pdfPath);
        $response->headers->set('Content-Type', 'application/pdf');
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_INLINE);

        return $response;
    }
    



}

