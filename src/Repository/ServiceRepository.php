<?php

namespace App\Repository;

use App\Entity\Service;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Service|null find($id, $lockMode = null, $lockVersion = null)
 * @method Service|null findOneBy(array $criteria, array $orderBy = null)
 * @method Service[]    findAll()
 * @method Service[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ServiceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Service::class);
    }

    // /**
    //  * @return Service[] Returns an array of Service objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Service
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function nbrService()
    {
        $services = $this->findAll();
        if ($services == null) {
            $services = [];
        }
        return count($services);
    }

    public function troisServicesLesPlusCommandee()
    {
        $services = $this->findAll();
        if ($services != null) {
            $values = [];
            $ses = [];
            $res = [];
            foreach ($services as $service) {
                # code...
                array_push($values, count($service->getCommandeService()));
                array_push($ses, $service);
            }
            $i = 0;
            while ($i < 3) {
                $k = $this->indexMax($values);
                if($k>=0){
                    array_push($res, $ses[$k]);
                $values[$k] = -1;
                }
                $i++;
            }
            //dd($res);
           // dd($res);
            return $res;
            
        }
        return null;
    }


    public function indexMax(array $p)
    {
        if(is_array($p)){
            $m=max($p);
            if($m>=0){
                $i = 0;
                while ($i < count($p)) {
                    if ($p[$i] == $m) {
                        return $i;
                    }
                    $i++;
                }    
            }
        }
        return -1;
    }
    public function trouverChaqueServiceNombreDeCommandeRecu()
    {
        $services = $this->findAll();
        if ($services != null) {
            $valeurs = [];
            foreach ($services as $service) {
                # code...
                array_push($valeurs, count($service->getCommandeService()));
            }
            return $valeurs;
        } else {
            return null;
        }
    }
    public function trouverNombreDeChaqueService()
    {
        $nbr = count($this->findAll());
        if ($nbr > 0) {
            $i = 0;
            $valeurs = [];
            while ($i < $nbr) {
                array_push($valeurs, $i);
                $i++;
            }
        }
    }

    public function trouverNomDeChaqueService()
    {
        $s = $this->findAll();
        if ($s != null) {
            $valeurs = [];
           foreach ($s as $i) {
               # code...
               array_push($valeurs,$i->getNom());
           }
           return $valeurs;
        }
        return null;
    }
}
