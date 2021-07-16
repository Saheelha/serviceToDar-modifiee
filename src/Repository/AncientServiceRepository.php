<?php

namespace App\Repository;

use App\Entity\AncientService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AncientService|null find($id, $lockMode = null, $lockVersion = null)
 * @method AncientService|null findOneBy(array $criteria, array $orderBy = null)
 * @method AncientService[]    findAll()
 * @method AncientService[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AncientServiceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AncientService::class);
    }

    // /**
    //  * @return AncientService[] Returns an array of AncientService objects
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
    public function findOneBySomeField($value): ?AncientService
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
