<?php

namespace App\Repository;

use App\Entity\CategorieMetier;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CategorieMetier|null find($id, $lockMode = null, $lockVersion = null)
 * @method CategorieMetier|null findOneBy(array $criteria, array $orderBy = null)
 * @method CategorieMetier[]    findAll()
 * @method CategorieMetier[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategorieMetierRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CategorieMetier::class);
    }

    // /**
    //  * @return CategorieMetier[] Returns an array of CategorieMetier objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?CategorieMetier
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
