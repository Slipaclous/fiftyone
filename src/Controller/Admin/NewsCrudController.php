<?php

namespace App\Controller\Admin;

use App\Entity\News;
use App\Entity\Categories;
use App\Form\Type\ImageUploadType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;


class NewsCrudController extends AbstractCrudController
{

    public static function getEntityFqcn(): string
    {
        return News::class;
    }


    public function configureFields(string $pageName): iterable
    {
        // ...
        
        yield TextField::new('titre');
        yield ImageField::new('cover')
            ->setBasePath('')
            ->setUploadDir('public/images')
            ->setUploadedFileNamePattern('[randomhash].[extension]')
            ->setRequired(false);
        yield TextField::new('SousTitre');
        yield TextEditorField::new('description');
        yield DateField::new('date');
        yield AssociationField::new('categorie')
        ->setFormType(EntityType::class)
        ->setFormTypeOptions([
            'class' => Categories::class,
            'choice_label' => 'nom',
        ])
        ->setRequired(true); 
        yield CollectionField::new('images')
            ->hideOnIndex()
            ->setEntryType(ImageUploadType::class);
          
        // ...
    }
}
