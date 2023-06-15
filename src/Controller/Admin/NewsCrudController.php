<?php

namespace App\Controller\Admin;

use App\Entity\News;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use App\Form\Type\ImageUploadType;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;

class NewsCrudController extends AbstractCrudController
{
    
    public static function getEntityFqcn(): string
    {
        return News::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('titre');
        yield TextField::new('SubTitle');
        yield TextEditorField::new('description');
        yield DateField::new('date');

        yield CollectionField::new('images')
            ->setEntryType(ImageUploadType::class);
            
    }
}
