<?php

namespace App\Controller;

use App\Repository\EventsRepository;
use Doctrine\ORM\EntityManagerInterface;
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
    $query = $eventsRepository->createQueryBuilder('e')->getQuery();
    
    $pagination = $paginator->paginate(
        $query,
        $request->query->getInt('page', 1), // Current page number, defaulting to 1
        8 // Number of items per page
    );

    return $this->render('events/index.html.twig', [
        'events' => $pagination,
    ]);
}

    #[Route('/events/{slug}', name: 'app_events_show')]
    public function show(string $slug, EventsRepository $eventsRepository): Response
    {
        $event = $eventsRepository->findOneBy(['slug' => $slug]);

        if (!$event) {
            throw $this->createNotFoundException('Event not found');
        }

        return $this->render('events/show.html.twig', [
            'event' => $event,
        ]);
    }
}
