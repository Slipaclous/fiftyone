<?php

namespace App\Controller;

use App\Repository\PresentationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PresentationController extends AbstractController
{
    #[Route('/presentation', name: 'app_presentation')]
    public function index(PresentationRepository $presentationRepository): Response
    {
        $presentation = $presentationRepository->findAll();
        return $this->render('home/presentation.html.twig', [
            'presentation' => $presentation,
        ]);
    }
}
