<?php

namespace App\Repository;

use App\Entity\Events;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Events>
 *
 * @method Events|null find($id, $lockMode = null, $lockVersion = null)
 * @method Events|null findOneBy(array $criteria, array $orderBy = null)
 * @method Events[]    findAll()
 * @method Events[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Events::class);
    }

    public function save(Events $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Events $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    public function findBySearchQuery(string $searchQuery): array
    {
        $queryBuilder = $this->createQueryBuilder('e')
            ->where('e.titre LIKE :query')
            ->orWhere('e.description LIKE :query')
            ->setParameter('query', '%' . $searchQuery . '%')
            ->orderBy('e.date', 'DESC');

        return $queryBuilder->getQuery()->getResult();
    }
    public function findClosestEvents(): array
    {
        // First, try to find upcoming events
        $queryBuilder = $this->createQueryBuilder('e')
            ->where('e.date >= :currentDate')
            ->setParameter('currentDate', new \DateTime())
            ->orderBy('e.date', 'ASC')
            ->setMaxResults(3);
    
        $upcomingEvents = $queryBuilder->getQuery()->getResult();
    
        // If there are upcoming events, return them
        if (count($upcomingEvents) > 0) {
            return $upcomingEvents;
        }
    
        // If no upcoming events are found, return the most recent past events
        return $this->createQueryBuilder('e')
            ->orderBy('e.date', 'DESC')
            ->setMaxResults(3)
            ->getQuery()
            ->getResult();
    }

//    /**
//     * @return Events[] Returns an array of Events objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('e.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Events
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
