<?php

namespace App\Repository;

use App\Entity\Visit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Visit>
 *
 * @method Visit|null find($id, $lockMode = null, $lockVersion = null)
 * @method Visit|null findOneBy(array $criteria, array $orderBy = null)
 * @method Visit[]    findAll()
 * @method Visit[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VisitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Visit::class);
    }

    public function save(Visit $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Visit $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function countVisits(): int
    {
        return $this->createQueryBuilder('v')
            ->select('COUNT(v.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countVisitsBetweenDates(\DateTimeInterface $startDate, \DateTimeInterface $endDate): int
    {
        return $this->createQueryBuilder('v')
            ->select('COUNT(v.id)')
            ->where('v.visitDate BETWEEN :startDate AND :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countUniqueVisitors(): int
    {
        return $this->createQueryBuilder('v')
            ->select('COUNT(DISTINCT v.visitorIP)')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
