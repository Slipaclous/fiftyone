<?php

namespace App\Controller;

use Exception;
use DateTimeZone;
use App\Entity\User;
use DateTimeImmutable;
use App\Entity\Message;
use App\Form\MessageType;
use Psr\Log\LoggerInterface;
use App\Repository\UserRepository;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MessageController extends AbstractController
{
    #[Route('/message', name: 'app_message')]
    public function index(): Response
    {
        return $this->render('message/index.html.twig', [
            'controller_name' => 'MessageController',
        ]);
    }

    
    // Crée un nouveau message.
#[Route('/message/new', name: 'message_new')]
public function newMessage(Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository): Response
{
    $message = new Message();
    $message->setIsRead(false); // Initialise le message comme non lu
    // Récupère tous les utilisateurs pour peupler le menu déroulant des destinataires
    $users = $userRepository->findAll();

    $form = $this->createForm(MessageType::class, $message);

    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Obtient l'utilisateur actuellement connecté comme expéditeur
        $sender = $this->getUser();

        // Définit l'expéditeur et la date et heure actuelles
        $message->setSender($sender);
        $createdAt = new DateTimeImmutable('now', new DateTimeZone('Europe/Paris'));
        $message->setCreatedAt($createdAt);
        // Renseigne les autres champs (par exemple, contenu, destinataire) selon vos besoins

        // Gère l'enregistrement du message
        $entityManager->persist($message);
        $entityManager->flush();

        $this->addFlash('success', 'Message envoyé avec succès!');
        return $this->redirectToRoute('member_area');
    }

    return $this->render('message/new.html.twig', [
        'form' => $form->createView(),
    ]);
}

// Répond à un message existant.
#[Route('/message/reply/{message}', name: 'message_reply')]
public function reply(Message $message, Request $request, EntityManagerInterface $entityManager, LoggerInterface $logger): Response
{
    // Récupère l'utilisateur destinataire en fonction du message
    $receiver = $message->getReceiver();

    $replyMessage = new Message();
    $replyMessage->setReceiver($receiver);

    $form = $this->createForm(MessageType::class, $replyMessage);

    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $sender = $this->getUser();
        $createdAt = new DateTimeImmutable('now', new DateTimeZone('Europe/Paris'));

        $replyMessage
            ->setSender($sender)
            ->setCreatedAt($createdAt)
            ->setContent($form['content']->getData());

        try {
            $entityManager->persist($replyMessage);
            $entityManager->flush();

            $this->addFlash('success', 'Réponse envoyée avec succès!');
            return $this->redirectToRoute('message_show_conversation', ['id' => $receiver->getId()]);
        } catch (Exception $e) {
            $this->addFlash('error', "Une erreur s'est produite lors de l'enregistrement du message : " . $e->getMessage());
            $logger->error("Erreur lors de l'enregistrement du message : " . $e->getMessage());
        }
    }

    return $this->render('message/reply.html.twig', [
        'form' => $form->createView(),
        'message' => $message,
    ]);
}

// Affiche les conversations de l'utilisateur.
#[Route('message/conversations', name: 'message_conversations')]
public function showConversations(MessageRepository $messageRepository): Response
{
    // Obtient l'utilisateur actuellement connecté
    $user = $this->getUser();

    // Si l'utilisateur n'est pas connecté ou introuvable, lance une exception appropriée ou gère le cas
    if (!$user) {
        throw $this->createNotFoundException('Utilisateur non trouvé');
    }

    // Récupère les conversations pour l'utilisateur actuel
    $conversations = $messageRepository->findConversationsForUser($user);

    // Extrait les utilisateurs des conversations et obtient le nombre de messages non lus
    $usersWithConversations = [];
    foreach ($conversations as $conversation) {
        $currentUser = $conversation['user'];
        $unreadCount = $messageRepository->getUnreadCountForUserInConversation($user, $currentUser);
        $usersWithConversations[] = [
            'user' => $currentUser,
            'unreadCount' => $unreadCount
        ];
    }

    return $this->render('message/conversations_list.html.twig', [
        'usersWithConversations' => $usersWithConversations,
    ]);
}
// Affiche une conversation spécifique avec un utilisateur donné.
#[Route('/conversation/{id}', name: 'message_show_conversation', requirements: ['id' => '\d+'])]
public function showConversation(int $id, MessageRepository $messageRepository, EntityManagerInterface $entityManager): Response
{
    // Récupère l'utilisateur actuellement connecté
    $user = $this->getUser();

    // Recherche l'utilisateur avec lequel la conversation doit être affichée
    $conversationUser = $entityManager->getRepository(User::class)->find($id);

    // Récupère les messages pour la conversation donnée
    $messages = $messageRepository->findMessagesForConversation($user, $conversationUser);

    // Marque les messages comme lus
    foreach ($messages as $message) {
        if (!$message->getIsRead() && $message->getReceiver() === $user) {
            $message->setIsRead(true);
            $entityManager->persist($message);
        }
    }
    $entityManager->flush();

    return $this->render('message/conversation.html.twig', [
        'messages' => $messages,
        'conversationUser' => $conversationUser,
    ]);
}

}


