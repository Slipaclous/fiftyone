<?php

namespace App\Controller;

use App\Entity\News;
use App\Entity\Comments;
use App\Form\CommentsType;
use App\Repository\NewsRepository;
use App\Repository\CommentsRepository;
use App\Repository\CategoriesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class NewsController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    // Page d'index des news
    #[Route("/news", name: "news_index")]
    public function index(NewsRepository $newsRepository, CategoriesRepository $categoriesRepository, Request $request, PaginatorInterface $paginator): Response
    {
        $selectedCategorySlug = $request->query->get('category');
        $selectedCategory = null;
        $searchQuery = $request->query->get('search');
        $lastnews = $newsRepository->findThirdLastNew();

        // Création de la requête pour récupérer les news
        $newsQueryBuilder = $newsRepository->createQueryBuilder('n')
            ->orderBy('n.date', 'DESC');

        if ($selectedCategorySlug) {
            $selectedCategory = $categoriesRepository->findOneBy(['slug' => $selectedCategorySlug]);
            $newsQueryBuilder->andWhere('n.categorie = :category')
                ->setParameter('category', $selectedCategory);
        }

        if ($searchQuery) {
            $newsQueryBuilder->andWhere('n.titre LIKE :searchQuery OR n.description LIKE :searchQuery')
                ->setParameter('searchQuery', '%' . $searchQuery . '%');
        }

        $newsQuery = $newsQueryBuilder->getQuery();

        // Récupération de la page courante depuis les paramètres de la requête
        $page = $request->query->getInt('page', 1);
        $perPage = 5; // Ajuster le nombre d'éléments par page selon les besoins
        $pagination = $paginator->paginate($newsQuery, $page, $perPage);

        $categories = $categoriesRepository->findAll();

        return $this->render('news/index.html.twig', [
            'news' => $pagination,
            'categories' => $categories,
            'selectedCategory' => $selectedCategory,
            'lastNews' => $lastnews,
        ]);
    }
    /**
     * Affichage d'une news et de ses commentaires
     *
     * @param string $slug
     * @param NewsRepository $newsRepository
     * @param CommentsRepository $commentsRepository
     * @param Request $request
     * @return Response
     */
    #[Route("/news/{slug}", name: "news_show")]
    public function show(string $slug, NewsRepository $newsRepository, CommentsRepository $commentsRepository, Request $request): Response
    {
        $news = $newsRepository->findOneBy(['slug' => $slug]);

        if (!$news) {
            throw $this->createNotFoundException('News not found');
        }

        // Création d'une nouvelle instance de l'entité Comment
        $comment = new Comments();
        $comment->setNews($news);
        $comment->setDateUpload(new \DateTime()); // Définition automatique de la date

        // Création du formulaire de commentaire
        $form = $this->createForm(CommentsType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Persister le commentaire en base de données
            $this->entityManager->persist($comment);
            $this->entityManager->flush();

                        // Redirection vers la même page de news pour éviter la soumission multiple
            return $this->redirectToRoute('news_show', ['slug' => $slug]);
        }

        // Récupération de la liste des commentaires associés à la news
        $comments = $commentsRepository->findByNews($news);

        return $this->render('news/show.html.twig', [
            'news' => $news,
            'comments' => $comments,
            'commentForm' => $form->createView(),
        ]);
    }

    
    /**
     * Suppression d'un commentaire
     *
     * @param string $id
     * @param string $slug
     * @param Comments $comment
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route("/delete-comment/{id}", name: "delete_comment", methods: ["POST"])]
    public function deleteComment(string $id, string $slug, Comments $comment, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($comment);
        $entityManager->flush();

        return $this->redirectToRoute('news_show', ['slug' => $slug]);
    }
}
