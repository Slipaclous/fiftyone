<?php

namespace App\Controller;

use App\Entity\User;
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
            $message->setCreatedAt(new \DateTime());

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
    #[Route('message/conversations', name: 'message_conversations')]
    public function showConversations(MessageRepository $messageRepository): Response
    {
        // Get the current logged-in user
        $user = $this->getUser();

        // Fetch conversations for the current user
        $conversations = $messageRepository->findConversationsForUser($user);

        return $this->render('message/conversations.html.twig', [
            'conversations' => $conversations,
        ]);
    }
    #[Route('/conversation/{id}', name: 'message_show_conversation')]
public function showConversation(Message $message,MessageRepository $messageRepository): Response
{
    // Get the sender and receiver from the message
    $sender = $message->getSender();
    $receiver = $message->getReceiver();

    // Assuming the conversation is between the sender and receiver
    // Fetch messages for the given conversation
    $messages = $messageRepository->findMessagesForConversation($sender, $receiver);

    return $this->render('message/conversation.html.twig', [
        'messages' => $messages,
    ]);
}
}


