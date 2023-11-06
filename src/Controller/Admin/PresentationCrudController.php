<?php

namespace App\Controller\Admin;

use App\Entity\Presentation;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

class PresentationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Presentation::class;
    }

    #[Route('/admin/presentation', name: 'admin_presentation')]
    #[Security ('is_granted("ROLE_ADMIN","ROLE_MEMBER")')]
    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextEditorField::new('text', 'Texte');
        yield ImageField::new('image', 'Image')
            ->setBasePath('/images')
            ->setUploadDir('public/images')
            ->setUploadedFileNamePattern('[randomhash].[extension]')
            ->setRequired(false);
    }
}
