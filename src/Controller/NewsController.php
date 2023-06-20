<?php

namespace App\Controller;

use App\Entity\News;
use App\Repository\NewsRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class NewsController extends AbstractController
{
    
      #[Route("/news", name:"news_index")]
     
    public function index(NewsRepository $newsRepository): Response
    {
        $news = $newsRepository->findAll();

        return $this->render('news/index.html.twig', [
            'news' => $news,
        ]);
    }

    
    #[Route("/news/{slug}", name:"news_show")]

    
    public function show(string $slug, NewsRepository $newsRepository): Response
{
    $news = $newsRepository->findOneBy(['slug' => $slug]);

    if (!$news) {
        throw $this->createNotFoundException('News not found');
    }

    return $this->render('news/show.html.twig', [
        'news' => $news,
    ]);
}
}

