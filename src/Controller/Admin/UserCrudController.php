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
    // Injection de dépendances pour les services nécessaires
    private AuthorizationCheckerInterface $authorizationChecker;
    private UserPasswordHasherInterface $passwordEncoder;
    private MailerInterface $mailer;

    // Constructeur avec les services injectés
    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker, 
        UserPasswordHasherInterface $passwordEncoder,
        MailerInterface $mailer
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->passwordEncoder = $passwordEncoder;
        $this->mailer = $mailer;
    }

    // Renvoie le FQCN de l'entité que ce contrôleur gère
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    // Configuration des champs pour les formulaires CRUD
    public function configureFields(string $pageName): iterable
    {
        // Configuration conditionnelle des champs selon la page
        if ($pageName !== Crud::PAGE_NEW) {
            $passwordField = TextField::new('password')
                ->setFormType(PasswordType::class)
                ->setRequired(false)
                ->hideOnIndex();

            // Désactiver le champ de mot de passe lors de l'édition
            if ($pageName === Crud::PAGE_EDIT) {
                $passwordField->setFormTypeOption('disabled', true);
            }
        }

        // Retourner les champs configurés pour le formulaire
        return [
            TextField::new('email', 'Email'),
            ChoiceField::new('roles', 'Roles')
                ->allowMultipleChoices()
                ->setChoices([
                    'Admin' => 'ROLE_ADMIN',
                    'User' => 'ROLE_MEMBER',
                ]),
            ChoiceField::new('fonction','fonction')
                    ->setChoices([
                        "Président"=>"president",
                        "Past-Président"=>"pastPresident",
                        "Vice-Président"=>"vicePresident",
                        "Responsable Protocole" => "responsableProtocole",
                        "Trésorier"=>"tresorier",
                        "Aide à la Communauté"=>"aideCommunaute",
                        "membre"=>"membre",
                        "administrateur"=>"administrateur",
                        "Secrétaire"=>"secretaire"

                    ])
        ];
    }

    // Persister une nouvelle entité dans la base de données
    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance ): void
    {
        // Vérification de l'autorisation de l'utilisateur
        if (!$this->authorizationChecker->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException('Accès refusé.');
        }

        // Gestion du mot de passe avant de persister l'entité
        $newPassword = $entityInstance->getPassword();
        if (!empty($newPassword)) {
            $hashedPassword = $this->passwordEncoder->hashPassword($entityInstance, $newPassword);
            $entityInstance->setPassword($hashedPassword);
        } else {
            // Définir un mot de passe temporaire si aucun n'est fourni
            $newPassword = 'temporary_password';
            $hashedPassword = $this->passwordEncoder->hashPassword($entityInstance, $newPassword);
            $entityInstance->setPassword($hashedPassword);
        }

        // Générer un token unique pour la vérification/réinitialisation du mot de passe
        if ($entityInstance instanceof User) {
            $token = bin2hex(random_bytes(32));
            $entityInstance->setPasswordResetToken($token);
            $entityInstance->setPasswordResetTokenExpiresAt(new DateTimeImmutable('+24 hours'));

            // Envoi d'un email pour configurer le mot de passe
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

        // Appeler la méthode parent pour persister l'entité
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

    // Configuration des actions disponibles pour l'entité User
    public function configureActions(Actions $actions): Actions
    {
        // Appeler la méthode parent pour obtenir les actions configurées par défaut
        $actions = parent::configureActions($actions);

        // Désactiver la création, la modification et la suppression pour les non-administrateurs
        if (!$this->authorizationChecker->isGranted('ROLE_ADMIN')) {
            $actions->disable(Action::NEW, Action::EDIT, Action::DELETE);
        }

        // Retourner les actions configurées
        return $actions;
    }
}
