<?php
namespace App\Controller;

use App\Repository\CarouselRepository;
use App\Repository\NewsRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(CarouselRepository $carouselRepository, NewsRepository $newsRepository): Response
    {
        $carouselImages = $carouselRepository->findAll();
        
        // Retrieve the third last news
        $news = $newsRepository->findThirdLastNews();

        return $this->render('home/index.html.twig', [
            'carouselImages' => $carouselImages,
            'news' => $news,
        ]);
    }
    #[Route('/presentation', name: 'app_presentation')]
    public function presentation():Response
    {
        return $this->render('home/presentation.html.twig');
    }

    #[Route('/mentions', name: 'app_mentions')]
    public function mentions():Response
    {
        return $this->render('home/mentions.html.twig');
    }
}
