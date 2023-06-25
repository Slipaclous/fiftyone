<?php

namespace App\Controller\Admin;

use App\Entity\Contact;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class ContactCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Contact::class;
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('email'),
            TextField::new('message'),
        ];
    }
    public function configureActions(Actions $actions): Actions
    {
        $actions = parent::configureActions($actions);

        $actions->disable(Action::NEW, Action::EDIT);
        if (!$this->isGranted('ROLE_ADMIN')) {
            $actions->disable( Action::DELETE);
        }

        return $actions;
    }
    
}
