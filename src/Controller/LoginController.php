<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends AbstractController
{
   
    #[Route('/login', name: 'app_login')]
    public function login( AuthenticationUtils $authenticationUtils):Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $username = $authenticationUtils->getLastUsername();

        return $this->render('login/login.html.twig', [
            'username' => $username,
            'error' => $error !== null,
        ]);
    }
    /**
     * Permet à l'utilisateur de se déconnecter
     *
     * @return void
     */
    #[Route("/logout", name:"app_logout")]
    public function logout(): void
    {
        // ..
    }
}
