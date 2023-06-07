<?php

namespace App\Controller\Admin;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class DashboardController extends AbstractDashboardController
{
    public function __construct(

        private AdminUrlGenerator $adminUrlGenerator
    ){

    }
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        $url = $this->adminUrlGenerator
            ->setController(NewsCrudController::class)
            ->setAction('index')
            ->generateUrl();
        

        return $this->redirect($url);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Fifty One');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::section('Gestion du site');

        yield MenuItem::subMenu('Actualités', 'fas fa-bars')->setSubItems([
            // MenuItem::linkToCrud('Liste des actualités', 'fas fa-list', News::class)
        
        ]);
        // yield MenuItem::linkToCrud('Liste des actualités', 'fas fa-list', News::class);
        
        
    }
}
