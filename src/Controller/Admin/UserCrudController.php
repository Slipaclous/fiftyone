<?php
namespace App\Controller\Admin;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class UserCrudController extends AbstractCrudController
{
    private AuthorizationCheckerInterface $authorizationChecker;
    private UserPasswordHasherInterface $passwordEncoder;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker, UserPasswordHasherInterface $passwordEncoder)
    {
        $this->authorizationChecker = $authorizationChecker;
        $this->passwordEncoder = $passwordEncoder;
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureFields(string $pageName): iterable
    {
        $passwordField = TextField::new('password')
            ->setFormType(PasswordType::class)
            ->setRequired(false)
            ->hideOnIndex();

        if ($pageName === Crud::PAGE_EDIT) {
            // Hide the password field in the edit form
            $passwordField->setFormTypeOption('disabled', true);
        }

        return [
            TextField::new('email'),
            // Show the avatar image
            ImageField::new('avatar')
                ->setBasePath('')
                ->setUploadDir('public/images')
                ->setUploadedFileNamePattern('[randomhash].[extension]')
                ->setRequired(false),
                
            // Add the description field
            TextareaField::new('informations')
                ->setLabel('Description'),

            // Add the role field
            ChoiceField::new('roles')
                ->allowMultipleChoices()
                ->setChoices([
                    'Admin' => 'ROLE_ADMIN',
                    'User' => 'ROLE_MEMBER',
                ])
                ->allowMultipleChoices(),

            $passwordField,
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
{
    // Only allow admins to create users
    if (!$this->authorizationChecker->isGranted('ROLE_ADMIN')) {
        throw new AccessDeniedException('Access Denied.');
    }

    $newPassword = $entityInstance->getPassword();

    if (!empty($newPassword)) {
        // Hash the new password before persisting the entity
        $hashedPassword = $this->passwordEncoder->hashPassword($entityInstance, $newPassword);
        $entityInstance->setPassword($hashedPassword);
    }

    parent::persistEntity($entityManager, $entityInstance);
}

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        // Only allow admins to edit users
        if (!$this->authorizationChecker->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException('Access Denied.');
        }

        // Remove the password field from the request to prevent it from being updated
        $request = Request::createFromGlobals();
        $request->request->remove('password');

        parent::updateEntity($entityManager, $entityInstance);
    }


    public function configureActions(Actions $actions): Actions
    {
        $actions = parent::configureActions($actions);

        if (!$this->isGranted('ROLE_ADMIN')) {
            $actions->disable(Action::NEW, Action::EDIT, Action::DELETE);
        }

        return $actions;
    }
}
