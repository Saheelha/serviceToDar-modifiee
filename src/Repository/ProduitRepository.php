<?php

namespace App\Repository;

use App\Entity\Produit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Produit|null find($id, $lockMode = null, $lockVersion = null)
 * @method Produit|null findOneBy(array $criteria, array $orderBy = null)
 * @method Produit[]    findAll()
 * @method Produit[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProduitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Produit::class);
    }

    // /**
    //  * @return Produit[] Returns an array of Produit objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */
    public function findCommandePerMonth(Produit $produit)
    {
        $query = $this->createQueryBuilder('p');
        $query->select('SUBSTRING(c.createdAt,1,10) as dt, count(p) as ct');
        $query->where('p.id=:id')
            ->leftJoin('p.commandeProduit', 'pc')
            ->leftJoin('pc.commande', 'c')
            ->andWhere('pc.=:id');
        $query->setParameter('id', $produit->getId());
        $query->groupBy('dt');
        return $query->getQuery()->getResult();;
    }

    /*
    public function findOneBySomeField($value): ?Produit
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    public function nbrProduit()
    {
        $produits = $this->findAll();
        if ($produits == null) {
            $produits = [];
        }
        return count($produits);
    }

    public function troisproduitLesPlusCommandee()
    {
        $produits = $this->findAll();
        if ($produits != null) {
            $values = [];
            $prod = [];
            $res = [];
            foreach ($produits as $produit) {
                # code...
                array_push($values, count($produit->getCommandeProduits()));
                array_push($prod, $produit);
            }
            $k=0;
            $i = 0;
            while ($i < 3) {
                $k = $this->indexMax($values);
               if($k>=0){
                array_push($res, $prod[$k]);
                $values[$k] = -1;
               }
                //unset($values[$k]);
                $i++;
            }
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

    public function trouverChaqueProduitNombreDeCommandeRecu()
    {
        $produits = $this->findAll();
        if ($produits != null) {
            $valeurs = [];
            foreach ($produits as $produit) {
                # code...
                array_push($valeurs, count($produit->getCommandeProduits()));
            }
            return $valeurs;
        } else {
            return null;
        }
    }

    public function trouverNombreDeChaqueProduit()
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

    public function trouverNomDeChaqueProduit()
    {
        $p = $this->findAll();
        if ($p != null) {
            $valeurs = [];
           foreach ($p as $i) {
               # code...
               array_push($valeurs,$i->getNom());
           }
           return $valeurs;
        }
        return null;
    }
}
