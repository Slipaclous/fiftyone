<?php

namespace App\Controller;

use DateTimeZone;
use App\Entity\User;
use DateTimeImmutable;
use App\Entity\Message;
use App\Form\MessageType;
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

    
    #[Route('/message/new', name: 'message_new')]
    public function newMessage(Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository): Response
    {
        $message = new Message();

        // Fetch users to populate the receiver dropdown
        $users = $userRepository->findAll();

        $form = $this->createForm(MessageType::class, $message);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Get the current logged-in user as the sender
            $sender = $this->getUser();

            // Set the sender and current datetime
            $message->setSender($sender);
            $createdAt = new DateTimeImmutable('now', new DateTimeZone('Europe/Paris'));
            $message->setCreatedAt($createdAt);
            // Populate other fields (e.g., content, receiver) based on your requirements

            // Handle saving the message
            $entityManager->persist($message);
            $entityManager->flush();

            $this->addFlash('success', 'Message sent successfully!');
            return $this->redirectToRoute('member_area');
        }

        return $this->render('message/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

public function reply(Request $request, EntityManagerInterface $entityManager, User $receiver): Response
{
    $message = new Message();
    $message->setReceiver($receiver);

    // Populate the form with receiver as default
    $form = $this->createForm(MessageType::class, $message);

    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Get the current logged-in user as the sender
        $sender = $this->getUser();

        // Set the sender and current datetime
        $message->setSender($sender);
        $createdAt = new DateTimeImmutable('now', new DateTimeZone('Europe/Paris'));
        $message->setCreatedAt($createdAt);
        // Populate other fields (e.g., content) based on your requirements

        // Handle saving the message
        $entityManager->persist($message);
        $entityManager->flush();

        $this->addFlash('success', 'Message sent successfully!');
        return $this->redirectToRoute('message_show_conversation', ['id' => $receiver->getId()]);
    }

    return $this->render('message/reply.html.twig', [
        'form' => $form->createView(),
    ]);
}
    #[Route('message/conversations', name: 'message_conversations')]
    public function showConversations(MessageRepository $messageRepository): Response
    {
    // Get the current logged-in user
    $user = $this->getUser();

    // Fetch conversations for the current user
    $conversations = $messageRepository->findConversationsForUser($user);

    // Extract users from conversations
    $usersWithConversations = [];
    foreach ($conversations as $conversation) {
        $usersWithConversations[] = $conversation['user'];
    }

    return $this->render('message/conversations_list.html.twig', [
        'usersWithConversations' => $usersWithConversations,
    ]);
}
#[Route('/conversation/{id}', name: 'message_show_conversation')]
public function showConversation(int $id, MessageRepository $messageRepository, EntityManagerInterface $entityManager): Response
{
    // Get the current logged-in user
    $user = $this->getUser();

    // Fetch the user with whom the conversation is to be displayed
    $conversationUser = $entityManager->getRepository(User::class)->find($id);

    // Fetch messages for the given conversation
    $messages = $messageRepository->findMessagesForConversation($user, $conversationUser);

    return $this->render('message/conversation.html.twig', [
        'messages' => $messages,
        'conversationUser' => $conversationUser,
    ]);
}

}


