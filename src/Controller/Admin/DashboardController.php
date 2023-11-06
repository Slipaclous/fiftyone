<?php

namespace App\Controller\Admin;

use App\Entity\News;
use App\Entity\User;
use App\Entity\Visit;
use App\Entity\Events;
use App\Entity\Contact;
use App\Entity\Carousel;
use App\Entity\Comments;
use App\Entity\Categories;
use App\Entity\Reservation;
use App\Entity\Presentation;
use App\Repository\VisitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use Symfony\Component\Security\Core\User\UserInterface;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;

class DashboardController extends AbstractDashboardController
{
    private VisitRepository $visitRepository;

    public function __construct(private AdminUrlGenerator $adminUrlGenerator, VisitRepository $visitRepository) 
    {
        $this->visitRepository = $visitRepository;
    }

    #[Route('/admin', name: 'admin')]
    public function dashboardIndex(EntityManagerInterface $entityManager): Response
    {
        if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_MEMBER')) {
            // Redirect the user to the 403 page
            return new Response($this->renderView('bundles/TwigBundle/Exception/error403.html.twig'), Response::HTTP_FORBIDDEN);
        }
        $totalUsers = $this->getTotalUsers($entityManager);
        $totalNews = $this->getTotalNews($entityManager);
        $totalVisits = $this->getTotalVisits(); 
        $visitorsLast7Days = $this->getVisitorsLast7Days();
        $visitorsLastMonth = $this->getVisitorsLastMonth();
        $totalUniqueVisitors = $this->getTotalUniqueVisitors();

        return $this->render('admin/dashboard.html.twig', [
            'totalUsers' => $totalUsers,
            'totalNews' => $totalNews,
            'totalVisits' => $totalVisits,
            'visitorsLast7Days' => $visitorsLast7Days,
            'visitorsLastMonth' => $visitorsLastMonth,
            'totalUniqueVisitors' => $totalUniqueVisitors,
        ]);
    }

    private function getVisitorsLast7Days(): int
{
    $endDate = new \DateTime();
    $startDate = (new \DateTime())->modify('-6 days');

    return $this->visitRepository->countVisitsBetweenDates($startDate, $endDate);
}
private function getTotalUniqueVisitors(): int
{
    // Retrieve total unique visitors count from your VisitRepository
    return $this->visitRepository->countUniqueVisitors();
}
private function getVisitorsLastMonth(): int
{
    $endDate = new \DateTime();
    $startDate = (new \DateTime())->modify('-1 month');

    return $this->visitRepository->countVisitsBetweenDates($startDate, $endDate);
}
    private function getTotalVisits(): int
    {
        // Retrieve total visits count from your VisitRepository
        return $this->visitRepository->count([]);
    }

    private function getTotalUsers(EntityManagerInterface $entityManager): int
    {
        // Retrieve total users count from your UserRepository or any other source
        return $entityManager->getRepository(User::class)->count([]);
    }

    private function getTotalNews(EntityManagerInterface $entityManager): int
    {
        // Retrieve total news count from your NewsRepository or any other source
        return $entityManager->getRepository(News::class)->count([]);
    }

    public function configureDashboard(): Dashboard
    {
        
        return Dashboard::new()
            ->setTitle('Fifty One');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToRoute('Retour au site', 'fa fa-home', 'app_home');
        yield MenuItem::section('Gestion du site');

        yield MenuItem::subMenu('Actualités', 'fas fa-bars')->setSubItems([
            MenuItem::linkToCrud('Liste des actualités', 'fas fa-list', News::class),
            MenuItem::linkToCrud('Ajouter une actualité', 'fas fa-plus', News::class)->setAction('new'),
        ]);

        yield MenuItem::linkToCrud('Images Slider', 'fas fa-images', Carousel::class);

        yield MenuItem::subMenu('Evènements', 'fas fa-bars')->setSubItems([
            MenuItem::linkToCrud('Liste des évènements', 'fas fa-list', Events::class),
            MenuItem::linkToCrud('Ajouter un évènement', 'fas fa-plus', Events::class)->setAction('new'),
        ]);

        yield MenuItem::subMenu('Utilisateurs', 'far fa-address-book')->setSubItems([
            MenuItem::linkToCrud('Liste des utilisateurs', 'fas fa-list', User::class),
            MenuItem::linkToCrud('Ajouter un utilisateur', 'fas fa-plus', User::class)->setAction('new'),
        ]);

        yield MenuItem::linkToCrud('Messages', 'fas fa-envelope', Contact::class);

        yield MenuItem::subMenu('Categories', 'fas fa-bars')->setSubItems([
            MenuItem::linkToCrud('Liste des catégories', 'fas fa-list', Categories::class),
            MenuItem::linkToCrud('Ajouter une catégorie', 'fas fa-plus', Categories::class)->setAction('new'),
        ]);

        yield MenuItem::linkToCrud('Commentaires', 'fas fa-comments', Comments::class);

        yield MenuItem::linkToCrud('Presentation', 'fas fa-info-circle', Presentation::class);

       ;
    }

    public function configureUserMenu(UserInterface $user): UserMenu
    {
        if(!$user instanceof User){
            throw new \Exception('User is not an instance of User class');
        }

        return parent::configureUserMenu($user)
            ->setName($user->getEmail())
            ->setAvatarUrl($user->getAvatar());
    }
}
