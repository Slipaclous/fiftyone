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
