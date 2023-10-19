<?php
namespace App\Controller\Admin;

use DateTime;
use App\Entity\User;
use DateTimeImmutable;
use Symfony\Component\Mime\Email;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class UserCrudController extends AbstractCrudController
{
    private AuthorizationCheckerInterface $authorizationChecker;
    private UserPasswordHasherInterface $passwordEncoder;
    private MailerInterface $mailer;
    
    public function __construct(AuthorizationCheckerInterface $authorizationChecker, UserPasswordHasherInterface $passwordEncoder,MailerInterface $mailer)
    {
        $this->authorizationChecker = $authorizationChecker;
        $this->passwordEncoder = $passwordEncoder;
        $this->mailer = $mailer;
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureFields(string $pageName): iterable
    {
        if ($pageName !== Crud::PAGE_NEW) {
            $passwordField = TextField::new('password')
                ->setFormType(PasswordType::class)
                ->setRequired(false)
                ->hideOnIndex();
    
            if ($pageName === Crud::PAGE_EDIT) {
                $passwordField->setFormTypeOption('disabled', true);
            }
        }

        return [
            TextField::new('email'),
            // Afficher l'image d'avatar
            // TextField::new('firstName'),
            // TextField::new('lastName'),
            // ImageField::new('avatar')
            //     ->setBasePath('')
            //     ->setUploadDir('public/images')
            //     ->setUploadedFileNamePattern('[randomhash].[extension]')
            //     ->setRequired(false)
            //     ->hideOnForm(),
                
            // Ajouter le champ de description
            // TextareaField::new('informations')
            //     ->setLabel('Description'),

            // Ajouter le champ de rôle
            ChoiceField::new('roles')
                ->allowMultipleChoices()
                ->setChoices([
                    'Admin' => 'ROLE_ADMIN',
                    'User' => 'ROLE_MEMBER',
                ])
                ->allowMultipleChoices(),

            // $passwordField,
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance ): void
    {
        // Autoriser uniquement les administrateurs à créer des utilisateurs
        if (!$this->authorizationChecker->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException('Accès refusé.');
        }

        $newPassword = $entityInstance->getPassword();
        $user = $this->getUser();
        if (!empty($newPassword)) {
            // Hasher le nouveau mot de passe avant de persister l'entité
            $hashedPassword = $this->passwordEncoder->hashPassword($entityInstance, $newPassword);
            $entityInstance->setPassword($hashedPassword);
            
        }
        if (empty($newPassword)) {
            $newPassword = 'temporary_password';
            $hashedPassword = $this->passwordEncoder->hashPassword($entityInstance, $newPassword);
            $entityInstance->setPassword($hashedPassword);
        }

        

    // Generate a unique token for user verification/resetting password.
    if ($entityInstance instanceof User) {
        $token = bin2hex(random_bytes(32));
        $entityInstance->setPasswordResetToken($token);
        $entityInstance->setPasswordResetTokenExpiresAt(new DateTimeImmutable('+24 hours'));
 // Token valid for 24 hours

        // Send email
        $email = (new TemplatedEmail())
            ->from('your_email@example.com')
            ->to($entityInstance->getEmail())
            ->subject('Set Up Your Password')
            ->htmlTemplate('email/setup_password.html.twig')
            ->context([
                'token' => $token,
            ]);

        $this->mailer->send($email);
    }
    parent::persistEntity($entityManager, $entityInstance);
    }


    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        // Autoriser uniquement les administrateurs à modifier les utilisateurs
        if (!$this->authorizationChecker->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException('Accès refusé.');
        }

        // Supprimer le champ de mot de passe de la requête pour empêcher sa mise à jour
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
