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

class EventsCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Events::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('titre');
        yield TextEditorField::new('description');
        yield DateField::new('date');
        yield IntegerField::new('places');

        yield CollectionField::new('images')
            ->setEntryType(ImageUploadType::class);
            
    }
}
