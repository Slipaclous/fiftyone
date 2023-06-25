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
    #[Route("/news", name: "news_index")]
public function index(NewsRepository $newsRepository, CategoriesRepository $categoriesRepository, Request $request, PaginatorInterface $paginator): Response
{
    $selectedCategorySlug = $request->query->get('category');
    $selectedCategory = null;

    if ($selectedCategorySlug) {
        $selectedCategory = $categoriesRepository->findOneBy(['slug' => $selectedCategorySlug]);
        $newsQuery = $newsRepository->createQueryBuilder('n')
            ->where('n.categorie = :category')
            ->setParameter('category', $selectedCategory)
            ->orderBy('n.date', 'DESC')
            ->getQuery();
    } else {
        $newsQuery = $newsRepository->createQueryBuilder('n')
            ->orderBy('n.date', 'DESC')
            ->getQuery();
    }

    // Get the current page from the query parameters
    $page = $request->query->getInt('page', 1);
    $perPage = 5; // Adjust the number of items per page as needed
    $pagination = $paginator->paginate($newsQuery, $page, $perPage);

    $categories = $categoriesRepository->findAll();

    return $this->render('news/index.html.twig', [
        'news' => $pagination,
        'categories' => $categories,
        'selectedCategory' => $selectedCategory,
    ]);
}
    

    

    #[Route("/news/{slug}", name: "news_show")]
public function show(string $slug, NewsRepository $newsRepository, CommentsRepository $commentsRepository, Request $request): Response
{
    $news = $newsRepository->findOneBy(['slug' => $slug]);

    if (!$news) {
        throw $this->createNotFoundException('News not found');
    }

    // Create a new instance of the Comment entity
    $comment = new Comments();
    $comment->setNews($news);
    $comment->setDateUpload(new \DateTime()); // Set the date automatically

    // Create the comment form
    $form = $this->createForm(CommentsType::class, $comment);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Persist the comment to the database
        $this->entityManager->persist($comment);
        $this->entityManager->flush();

        // Redirect to the same news page to avoid resubmission
        return $this->redirectToRoute('news_show', ['slug' => $slug]);
    }

    // Fetch the list of comments associated with the news
    $comments = $commentsRepository->findByNews($news);

    return $this->render('news/show.html.twig', [
        'news' => $news,
        'comments' => $comments,
        'commentForm' => $form->createView(),
    ]);
}
#[Route("/delete-comment/{id}", name: "delete_comment", methods: ["POST"])]

public function deleteComment(string $id, string $slug, Comments $comment, EntityManagerInterface $entityManager): Response
{
    $entityManager->remove($comment);
    $entityManager->flush();

    return $this->redirectToRoute('news_show', ['slug' => $slug]);
}

}




