<?php

namespace App\Controller\Member;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MemberAreaController extends AbstractController
{
    #[Route('/member/area', name: 'member_area')]
    #[IsGranted('ROLE_USER')]
    public function index(): Response
    {
        return $this->render('member/index.html.twig', [
            'controller_name' => 'MemberAreaController',
        ]);
    }
}
