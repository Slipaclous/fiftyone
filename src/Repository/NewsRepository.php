<?php

namespace App\Repository;

use App\Entity\News;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<News>
 *
 * @method News|null find($id, $lockMode = null, $lockVersion = null)
 * @method News|null findOneBy(array $criteria, array $orderBy = null)
 * @method News[]    findAll()
 * @method News[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NewsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, News::class);
    }

    public function save(News $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    public function findThirdLastNews()
{
    return $this->createQueryBuilder('n')
        ->orderBy('n.date', 'DESC')
        ->setMaxResults(2)
        ->getQuery()
        ->getResult();
}
    public function findThirdLastNew()
{
    return $this->createQueryBuilder('n')
        ->orderBy('n.date', 'DESC')
        ->setMaxResults(3)
        ->getQuery()
        ->getResult();
}
public function findNextThreeNews()
{
    return $this->createQueryBuilder('n')
        ->orderBy('n.date', 'DESC')
        ->setFirstResult(2) // Skip the first two news items
        ->setMaxResults(3) // Get the next three news items after the first two
        ->getQuery()
        ->getResult();
}


    public function remove(News $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function searchByKeywordAndCategory($keyword, $categoryId)
{
    $queryBuilder = $this->createQueryBuilder('n');

    if (!empty($keyword)) {
        $queryBuilder->andWhere('n.titre LIKE :keyword OR n.description LIKE :keyword')
            ->setParameter('keyword', '%' . $keyword . '%');
    }

    if (!empty($categoryId)) {
        $queryBuilder->andWhere('n.categorie_id = :categoryId')
            ->setParameter('categoryId', $categoryId);
    }

    return $queryBuilder->getQuery()->getResult();
}

public function findByCategory(?string $category): array
{
    $qb = $this->createQueryBuilder('n')
        ->leftJoin('n.categorie', 'c')
        ->andWhere('c.slug = :category')
        ->setParameter('category', $category);

    return $qb->getQuery()->getResult();
}

public function findBySearchQuery(string $searchQuery): array
{
    $queryBuilder = $this->createQueryBuilder('n')
        ->where('n.titre LIKE :query')
        ->orWhere('n.description LIKE :query')
        ->setParameter('query', '%' . $searchQuery . '%')
        ->orderBy('n.date', 'DESC');

    return $queryBuilder->getQuery()->getResult();
}

//    /**
//     * @return News[] Returns an array of News objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('n')
//            ->andWhere('n.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('n.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?News
//    {
//        return $this->createQueryBuilder('n')
//            ->andWhere('n.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
