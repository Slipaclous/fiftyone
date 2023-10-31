<?php

namespace App\Controller\Admin;

use App\Entity\Events;
use App\Form\Type\ImageUploadType;
use Symfony\Component\Routing\Annotation\Route;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class EventsCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Events::class;
    }
    #[Route('/admin/events', name: 'admin_events')]
    #[Security ('is_granted("ROLE_ADMIN","ROLE_MEMBER")')]
    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('titre');
        yield ImageField::new('cover')
            ->setBasePath('/images')
            ->setUploadDir('public/images')
            ->setUploadedFileNamePattern('[randomhash].[extension]')
            ->setRequired(false);
        yield TextEditorField::new('description');
        yield DateField::new('date');
        yield IntegerField::new('places');

        yield CollectionField::new('images')
            ->setEntryType(ImageUploadType::class);
            
    }
    public function configureActions(Actions $actions): Actions
{
    $printReservations = Action::new('printReservations', 'Print Reservations', 'fa fa-print')
        ->linkToRoute('admin_print_reservations', function (Events $event): array {
            return ['id' => $event->getId()];
        });
    return parent::configureActions($actions)
        ->add(Crud::PAGE_INDEX, $printReservations)
        ->add(Crud::PAGE_INDEX, Action::DETAIL);
}
public function index(AdminContext $context)
{
    // You can modify this method if you need to change the behavior of the event listing
    $response = parent::index($context);

    return $response;
}

public function detail(AdminContext $context)
{
    $event = $context->getEntity()->getInstance();
    $reservations = $event->getReservations();

    return $this->render('admin/events/detail.html.twig', [
        'event' => $event,
        'reservations' => $reservations,
    ]);
}
}
