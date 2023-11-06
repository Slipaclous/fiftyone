<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Message;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Message>
 *
 * @method Message|null find($id, $lockMode = null, $lockVersion = null)
 * @method Message|null findOneBy(array $criteria, array $orderBy = null)
 * @method Message[]    findAll()
 * @method Message[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Message::class);
    }


        // Cette fonction trouve toutes les conversations pour un utilisateur donné.
    public function findConversationsForUser(User $user): array
    {
        $conversations = [];

        // Regroupe les messages par paire d'expéditeur et de destinataire.
        $messages = $this->createQueryBuilder('m')
            ->andWhere('m.sender = :user OR m.receiver = :user')
            ->setParameter('user', $user)
            ->orderBy('m.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        foreach ($messages as $message) {
            // Détermine avec qui l'utilisateur a la conversation.
            $conversationUser = $message->getSender() === $user
                ? $message->getReceiver()
                : $message->getSender();

            $conversationUserId = $conversationUser->getId();
            $conversationContent = $message->getContent();

            // Crée une nouvelle conversation si elle n'existe pas déjà.
            if (!isset($conversations[$conversationUserId])) {
                $conversations[$conversationUserId] = [
                    'user' => $conversationUser,
                    'messages' => []
                ];
            }

            // Ajoute le message à la conversation existante.
            $conversations[$conversationUserId]['messages'][] = [
                'content' => $conversationContent,
                'createdAt' => $message->getCreatedAt()
            ];
        }

        return $conversations;
    }

    // Cette fonction récupère tous les messages pour une conversation entre deux utilisateurs.
    public function findMessagesForConversation(User $user1, User $user2): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('(m.sender = :user1 AND m.receiver = :user2) OR (m.sender = :user2 AND m.receiver = :user1)')
            ->setParameter('user1', $user1)
            ->setParameter('user2', $user2)
            ->orderBy('m.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    // Cette fonction compte le nombre de messages non lus pour un utilisateur dans une conversation.
    public function getUnreadCountForUserInConversation(User $recipient, User $sender): int
    {
        return $this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->where('m.receiver = :receiver')
            ->andWhere('m.sender = :sender')
            ->andWhere('m.isRead = false') // On suppose que 'isRead' est une colonne booléenne.
            ->setParameter('receiver', $recipient)
            ->setParameter('sender', $sender)
            ->getQuery()
            ->getSingleScalarResult();
    }
    public function countUnreadMessagesForUser(User $user): int
    {
        // Create a QueryBuilder to count messages
        $qb = $this->createQueryBuilder('m');
        $qb->select('count(m.id)')
           ->where('m.receiver = :user')
           ->andWhere('m.isRead = :isRead')
           ->setParameter('user', $user)
           ->setParameter('isRead', false);
    
        return (int) $qb->getQuery()->getSingleScalarResult();
    }

//    /**
//     * @return Message[] Returns an array of Message objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('m.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Message
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}