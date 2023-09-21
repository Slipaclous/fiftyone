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


    public function findConversationsForUser(User $user): array
    {
        $conversations = [];

        // Group messages by sender and receiver pairs
        $messages = $this->createQueryBuilder('m')
            ->andWhere('m.sender = :user OR m.receiver = :user')
            ->setParameter('user', $user)
            ->orderBy('m.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        foreach ($messages as $message) {
            $conversationUser = $message->getSender() === $user
                ? $message->getReceiver()
                : $message->getSender();

            $conversationUserId = $conversationUser->getId();
            $conversationContent = $message->getContent();

            if (!isset($conversations[$conversationUserId])) {
                $conversations[$conversationUserId] = [
                    'user' => $conversationUser,
                    'messages' => []
                ];
            }

            $conversations[$conversationUserId]['messages'][] = [
                'content' => $conversationContent,
                'createdAt' => $message->getCreatedAt()
            ];
        }

        return $conversations;
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
