<?php

namespace App\Repository;

use App\Entity\FournisseurFavoris;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method FournisseurFavoris|null find($id, $lockMode = null, $lockVersion = null)
 * @method FournisseurFavoris|null findOneBy(array $criteria, array $orderBy = null)
 * @method FournisseurFavoris[]    findAll()
 * @method FournisseurFavoris[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FournisseurFavorisRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FournisseurFavoris::class);
    }

    // /**
    //  * @return FournisseurFavoris[] Returns an array of FournisseurFavoris objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('f.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?FournisseurFavoris
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
