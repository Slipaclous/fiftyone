<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ErrorController extends AbstractController
{
    #[Route('/{url}', name: 'app_error')]
    public function notFound(): Response
    {
        return new Response($this->renderView('error/404.html.twig'), Response::HTTP_NOT_FOUND);
    }
}
