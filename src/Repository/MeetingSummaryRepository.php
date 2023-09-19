<?php

namespace App\Repository;

use App\Entity\MeetingSummary;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MeetingSummary>
 *
 * @method MeetingSummary|null find($id, $lockMode = null, $lockVersion = null)
 * @method MeetingSummary|null findOneBy(array $criteria, array $orderBy = null)
 * @method MeetingSummary[]    findAll()
 * @method MeetingSummary[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MeetingSummaryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MeetingSummary::class);
    }
    public function findMostRecentMeetingSummary(): ?MeetingSummary
    {
        return $this->createQueryBuilder('ms')
            ->orderBy('ms.date', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

//    /**
//     * @return MeetingSummary[] Returns an array of MeetingSummary objects
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

//    public function findOneBySomeField($value): ?MeetingSummary
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
