<?php

namespace App\Repository;

use App\Entity\CommandeService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CommandeService|null find($id, $lockMode = null, $lockVersion = null)
 * @method CommandeService|null findOneBy(array $criteria, array $orderBy = null)
 * @method CommandeService[]    findAll()
 * @method CommandeService[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommandeServiceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CommandeService::class);
    }

    // /**
    //  * @return CommandeService[] Returns an array of CommandeService objects
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
    public function findOneBySomeField($value): ?CommandeService
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
