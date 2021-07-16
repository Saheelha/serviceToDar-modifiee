<?php

namespace App\Repository;

use App\Entity\Commande;
use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Commande|null find($id, $lockMode = null, $lockVersion = null)
 * @method Commande|null findOneBy(array $criteria, array $orderBy = null)
 * @method Commande[]    findAll()
 * @method Commande[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommandeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Commande::class);
    }

    // /**
    //  * @return Commande[] Returns an array of Commande objects
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
    public function findOneBySomeField($value): ?Commande
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    public function trouverCommandeTrieParDate(Utilisateur $user){
        $queryBuilder=$this->createQueryBuilder('c')
        ->leftJoin('c.utilisateur','utilisateur')
        ->where('utilisateur.id=:id')
        ->setParameter('id',$user->getId())
        ->orderBy('c.createdAt','DESC')
        ;
        $query=$queryBuilder->getQuery();
        return $query->execute();
    }

    public function trouverCommandeServiceEnAttentes(Utilisateur $user){
        $queryBuilder=$this->createQueryBuilder('c');
        ;
    }
    public function commandeProduitEnAttente(Utilisateur $user){
        $produits=$user->getProduits();
        $commandes=[];
        foreach ($produits as $produit) {
            # code...
            foreach ($produit->getCommandeProduit() as $commandeProduit) {
                # code...
                $commande=$commandeProduit->getCommande();
                if($commande->getEtat()=='En attente'){
                    array_push($commandes,$commande);
                   //$commandes[$commande->getId()]=$commande;
                }
            }
        }
        return $commandes;

    }

    public function commandeServiceEnAttente(Utilisateur $user){
        $services=$user->getServices();
        $commandes=[];
        foreach ($services as $service) {
            # code...
            foreach ($service->getCommandeService() as $commandeService) {
                # code...
                $commande=$commandeService->getCommande();
                if($commande->getEtat()=='En attente'){
                    array_push($commandes,$commande);
                   //$commandes[$commande->getId()]=$commande;
                }
            }
        }
        return $commandes;

    }
    public function commandeTrie(Utilisateur $user){
        $queryBuilder=$this->createQueryBuilder('c');
        $queryBuilder->where("c.etat=:etat")
        ->setParameter('etat','En attente')
        ->orderBy('c.id','ASC')
        ;
        $query=$queryBuilder->getQuery();
        $commandes=$query->getResult();
       // dd($commandes);
        $commandeTrie=[];
        foreach ($commandes as $c) {
            # code...
            if($c->getServiceCommande()!=null){
                foreach ($c->getServiceCommande() as $commandeService) {
                //     # code...
                //    if($commandeService->getService()->getUtilisateur()->getId()==$user->getId()){
                //     //$commande=$commandeService->getCommande();
                //     array_push($commandeTrie,$c);
                //    }
                if($commandeService->getService()!=null){
                    $service=$commandeService->getService();
                }
                if($commandeService->getAncientService()!=null){
                    $service=$commandeService->getAncientService();
                }
                if($service->getUtilisateur()->getId()==$user->getId()){
                    array_push($commandeTrie,$c);
                }
                }
            }
            if($c->getCommandeProduit()!= null){
                foreach ($c->getCommandeProduit() as $commandeProduit) {
                    # code...
                    
                    // if($commandeProduit->getProduit()->getUtilisateur()->getId()||$commandeProduit->getAncientProduit()->getUtilisateur()->getId()==$user->getId()){
                    //     //$commande=$commandeProduit->getCommande();
                    //     array_push($commandeTrie,$c);
                    //    }
                    $produit=$commandeProduit->getProduit()||$commandeProduit->getAncientProduit();
                    if($commandeProduit->getProduit()!=null){
                        $produit=$commandeProduit->getProduit();
                    }
                    if($commandeProduit->getAncientProduit()!=null){
                        $produit=$commandeProduit->getAncientProduit();
                    }
                    if($produit->getUtilisateur()->getId()==$user->getId()){
                        array_push($commandeTrie,$c);
                    }
                }
            }
        }
        return $commandeTrie;
    }



    public function commandeTrieTermine(Utilisateur $user){
      //  $commandes=$this->findBy(['etat'=>'Termine']);
        $queryBuilder=$this->createQueryBuilder('c');
        $queryBuilder->where("c.etat=:etat")
        ->setParameter('etat','Termine')
        ->orderBy('c.id','DESC')
        ;
        //dd($commandes);
        $commandeTrie=[];
        $query=$queryBuilder->getQuery();
        $commandes=$query->getResult();
       // dd($commandes);
       $commandeTrie=[];
       foreach ($commandes as $c) {
           # code...
           if($c->getServiceCommande()!=null){
               foreach ($c->getServiceCommande() as $commandeService) {
               //     # code...
               //    if($commandeService->getService()->getUtilisateur()->getId()==$user->getId()){
               //     //$commande=$commandeService->getCommande();
               //     array_push($commandeTrie,$c);
               //    }
               if($commandeService->getService()!=null){
                   $service=$commandeService->getService();
               }
               if($commandeService->getAncientService()!=null){
                   $service=$commandeService->getAncientService();
               }
               if($service->getUtilisateur()->getId()==$user->getId()){
                   array_push($commandeTrie,$c);
               }
               }
           }
           if($c->getCommandeProduit()!= null){
               foreach ($c->getCommandeProduit() as $commandeProduit) {
                   # code...
                   
                   // if($commandeProduit->getProduit()->getUtilisateur()->getId()||$commandeProduit->getAncientProduit()->getUtilisateur()->getId()==$user->getId()){
                   //     //$commande=$commandeProduit->getCommande();
                   //     array_push($commandeTrie,$c);
                   //    }
                   $produit=$commandeProduit->getProduit()||$commandeProduit->getAncientProduit();
                   if($commandeProduit->getProduit()!=null){
                       $produit=$commandeProduit->getProduit();
                   }
                   if($commandeProduit->getAncientProduit()!=null){
                       $produit=$commandeProduit->getAncientProduit();
                   }
                   if($produit->getUtilisateur()->getId()==$user->getId()){
                       array_push($commandeTrie,$c);
                   }
               }
           }
       }
        return $commandeTrie;
    }
}
