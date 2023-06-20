<?php

namespace App\Controller\Admin;

use App\Entity\Events;
use App\Form\Type\ImageUploadType;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class EventsCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Events::class;
    }
    #[Security ('is_granted("ROLE_ADMIN","ROLE_MEMBER")')]
    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('titre');
        yield ImageField::new('cover')
            ->setBasePath('')
            ->setUploadDir('public/images')
            ->setUploadedFileNamePattern('[randomhash].[extension]')
            ->setRequired(false);
        yield TextEditorField::new('description');
        yield DateField::new('date');
        yield IntegerField::new('places');

        yield CollectionField::new('images')
            ->setEntryType(ImageUploadType::class);
            
    }
}
