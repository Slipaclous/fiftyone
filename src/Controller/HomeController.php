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
}
