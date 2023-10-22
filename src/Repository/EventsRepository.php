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
    // Try to find upcoming events
    $queryBuilder = $this->createQueryBuilder('e')
        ->where('e.date >= :currentDate')
        ->setParameter('currentDate', new \DateTime())
        ->orderBy('e.date', 'ASC');

    $upcomingEvents = $queryBuilder->getQuery()->getResult();

    $numUpcomingEvents = count($upcomingEvents);

    // If there are 3 or more upcoming events, return them
    if ($numUpcomingEvents >= 3) {
        return $upcomingEvents;
    }

    // If fewer than 3 upcoming events, fetch past events to fill the gap
    $numPastEventsNeeded = 3 - $numUpcomingEvents;
    $pastEvents = $this->createQueryBuilder('e')
        ->where('e.date < :currentDate')
        ->setParameter('currentDate', new \DateTime())
        ->orderBy('e.date', 'DESC')
        ->setMaxResults($numPastEventsNeeded)
        ->getQuery()
        ->getResult();

    // Return a mix of upcoming and past events
    return array_merge($upcomingEvents, array_reverse($pastEvents));
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
