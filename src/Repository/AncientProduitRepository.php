<?php

namespace App\Repository;

use App\Entity\AncientProduit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AncientProduit|null find($id, $lockMode = null, $lockVersion = null)
 * @method AncientProduit|null findOneBy(array $criteria, array $orderBy = null)
 * @method AncientProduit[]    findAll()
 * @method AncientProduit[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AncientProduitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AncientProduit::class);
    }

    // /**
    //  * @return AncientProduit[] Returns an array of AncientProduit objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?AncientProduit
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
