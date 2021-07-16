<?php

namespace App\Repository;

use App\Entity\Conversation;
use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Validator\Constraints\NotNull;

/**
 * @method Conversation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Conversation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Conversation[]    findAll()
 * @method Conversation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConversationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Conversation::class);
    }

    // /**
    //  * @return Conversation[] Returns an array of Conversation objects
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
    public function findOneBySomeField($value): ?Conversation
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    public function findConversationByUsers( Utilisateur $user, Utilisateur $autherUser){
        $entityManager=$this->getEntityManager();
       // $query='SELECT c.id c.titre FROM conversation AS c, utilisateur AS u,utilisateur_conversation AS cu WHERE cu.conversation_id=c.id and u.id=cu.utilisateur_id and (u.id=:d1 and u.id=:d2)';
        $query="SELECT conversation.titre,conversation.id FROM conversation,utilisateur_conversation,utilisateur
        WHERE conversation.id=utilisateur_conversation.conversation_id  AND utilisateur.id=utilisateur_conversation.utilisateur_id and (utilisateur.id=:d1 or utilisateur.id=:d2)";
        $stmt=$entityManager->getConnection()->prepare($query);
       $stmt->execute(['d1'=>$user->getId(),'d2'=>$autherUser->getId()]);
        //dd($stmt->fetchAllAssociative());
         $resultat=$stmt->fetchAllAssociative();
        if(empty($resultat)){
             return null;
         }
         else{
            // return $resultat['id'];
             $this->findBy(['id'=>$resultat[0]['id']]);
         }
        
    }
    public function findConversationsByLastMessage(Utilisateur $user){
        $queryBuilder=$this->createQueryBuilder('c');
        $queryBuilder->leftJoin('c.utilisateurs','utilisateur')
        ->where('utilisateur.id=:id')
        //->andWhere('utilisateur.abonnement=:t')
        ->setParameter('id',$user->getId())
        //->setParameter('t',1)
        ->orderBy('c.dateDernierMessage','DESC')
        ;
        $query=$queryBuilder->getQuery();
        return $query->getResult();
    }
    /**
     * //$queryBuilder=$this->createQueryBuilder('');
        $queryBuilder=$this->select('u.conversations');
        $queryBuilder->from('Utilisateur','u');
        //$queryBuilder->where('c.dateDernierMessage != null');
        //$queryBuilder->andWhere('c.utilisateur = :id');
        $queryBuilder->where('u.id = :id');
        $queryBuilder->setParameter('id',$user->getId());
        //$queryBuilder->orderBy('c.dateDernierMessage','DESC');
        $query=$queryBuilder->getQuery();
        $resultat=$query->getResult();
        return $resultat;
     */
    public function trouverConversation(Utilisateur $user,Utilisateur $otherUser){
        $conversations=$user->getConversations();
        foreach ($conversations as $c) {
            # code...
            foreach ($c->getUtilisateurs() as $u) {
                # code...
                if ($u->getId() == $otherUser->getId()) {
                    return $c;
                }
            }
        }
        return null;
    }
}
