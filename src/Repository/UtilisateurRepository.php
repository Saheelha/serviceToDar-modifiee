<?php

namespace App\Repository;

use App\Entity\Utilisateur;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Encoder\JsonEncode;

/**
 * @method Utilisateur|null find($id, $lockMode = null, $lockVersion = null)
 * @method Utilisateur|null findOneBy(array $criteria, array $orderBy = null)
 * @method Utilisateur[]    findAll()
 * @method Utilisateur[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UtilisateurRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Utilisateur::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(UserInterface $user, string $newEncodedPassword): void
    {
        if (!$user instanceof Utilisateur) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newEncodedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }

    // /**
    //  * @return Utilisateur[] Returns an array of Utilisateur objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Utilisateur
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    public function findAllParDate(){
        $queryBuilder=$this->createQueryBuilder('c');
        $queryBuilder->groupBy('c.dateCreations');
        $query=$queryBuilder->getQuery();
        $res=$query->getResult();
        $tab=[0,0,0,0,0,0,0,0,0,0,0,0];
        $year=new DateTime('y');
        foreach ($res as $r) {
            # code...
            if($r->getDateCreation()->format('m')=='1'){
                $tab[0]+=1;
            }
            if($r->getDateCreation()->format('m')=='2'){
                $tab[1]+=1;
            }
            if($r->getDateCreation()->format('m')=='3'){
                $tab[2]+=1;
            }
            if($r->getDateCreation()->format('m')=='4'){
                $tab[3]+=1;
            }
            if($r->getDateCreation()->format('m')=='5'){
                $tab[4]+=1;
            }
            if($r->getDateCreation()->format('m')=='6'){
                $tab[5]+=1;
            }
            if($r->getDateCreation()->format('m')=='7'){
                $tab[6]+=1;
            }
            if($r->getDateCreation()->format('m')=='8'){
                $tab[7]+=1;
            }
            if($r->getDateCreation()->format('m')=='9'){
                $tab[8]+=1;
            }
            if($r->getDateCreation()->format('m')=='10'){
                $tab[9]+=1;
            }
            if($r->getDateCreation()->format('m')=='11'){
                $tab[10]+=1;
            }
            if($r->getDateCreation()->format('m')=='12'){
                $tab[11]+=1;
            }
        }

    }
    public function nbrFourniseurs(){
        
        $fournisseurs=$this->findAll();
        $val=[];
        //$fournisseurs=$this->findByRoles(['ROLE_FOURNISSEUR']);
        if($fournisseurs== null){
            $fournisseurs=[];
        }
        else{
            foreach ($fournisseurs as $fournisseur) {
                # code...
                foreach ($fournisseur->getRoles() as $role) {
                    # code...
                    if($role=='ROLE_FOURNISSEUR'){
                        array_push($val,$fournisseur);
                    }
                }
            }
        }
        return count($val);
    }

    public function nbrClients(){
        $clients=$this->findAll();
        $val=[];
        if($clients== null){
            $clients=[];
        }
        else{
            foreach ($clients as $client) {
                # code...
                foreach ($client->getRoles() as $role) {
                    # code...
                    if($role=='ROLE_CLIENT'){
                        array_push($val,$client);
                    }
                }
            }
        }
        return count($val);
    }

    public function trouverClients(){
       $utilisateurs=$this->findAll();
       $c=[];
       foreach ($utilisateurs as $client) {
           # code...
           foreach ($client->getRoles() as $role) {
            # code...
            if($role=='ROLE_CLIENT'){
                array_push($c,$client);
            }
        }
       }
      return $c;
        /*
          return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
        */
    }
    public function trouverFournisseurs(){
        $utilisateurs=$this->findAll();
        $f=[];
        foreach ($utilisateurs as $fournisseur) {
            # code...
            foreach ($fournisseur->getRoles() as $role) {
             # code...
             if($role=='ROLE_FOURNISSEUR'){
                 array_push($f,$fournisseur);
             }
         }
        }
       return $f;
    }
}
