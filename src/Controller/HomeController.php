<?php
namespace App\Controller;

use App\Repository\CarouselRepository;
use App\Repository\NewsRepository;
use App\Repository\UserRepository;
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
        $news = $newsRepository->findThirdLastNew();
        // Retrieve the next three news
        $newsi = $newsRepository->findNextThreeNews();

        return $this->render('home/index.html.twig', [
            'carouselImages' => $carouselImages,
            'news' => $news,
            'newsi' => $newsi,
        ]);
    }

    #[Route('/fifty-one-enghien', name:'app_club')]
    public function club(UserRepository $users):Response
    {
        $user=$users->findAll();
        $functionOrder = [
            'president' => 1,
            'pastPresident' => 2,
            'vicePresident' => 3,
            'responsableProtocole' => 4,
            'tresorier' => 5,
            'aideCommunaute' => 6,
            'secretaire'=>7
        ];

        usort($user, function ($a, $b) use ($functionOrder) {
            $aOrder = $functionOrder[$a->getFonction()] ?? PHP_INT_MAX;
            $bOrder = $functionOrder[$b->getFonction()] ?? PHP_INT_MAX;
            return $aOrder - $bOrder;
        });

        return $this->render('home/club.html.twig',[
            'users'=> $user
        ]);
    }
    // Route pour la page de présentation
    #[Route('/presentation', name: 'app_presentation')]
    public function presentation():Response
    {
        return $this->render('home/presentation.html.twig');
    }

    #[Route('/conact', name: 'app_contact')]
    public function mentions():Response
    {
        return $this->render('contact/contact.html.twig');
    }
    #[Route('/devenir-fifty-oner', name: 'app_oner')]
    public function fiftyoner():Response
    {
        return $this->render('home/oner.html.twig');
    }

    //Route pour la page de mentions légales
    #[Route('/mentions', name: 'app_mentions')]
    public function contact():Response
    {
        return $this->render('home/mentions.html.twig');
    }
}
