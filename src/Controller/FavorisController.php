<?php

namespace App\Controller;

use App\Entity\Favoris;
use App\Entity\Utilisateur;
use App\Repository\FavorisRepository;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use MercurySeries\FlashyBundle\FlashyNotifier;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class FavorisController extends AbstractController
{
    /**
     * @IsGranted("ROLE_CLIENT",message="Seul un client peut accéder a ce contenu !  ")
     * @Route("/mes_favoris/{user}", name="app_consulter_favoris")
     */
    public function consulterFavoris(Utilisateur $user,UtilisateurRepository $utilisateurRepository): Response
    {

        $favoris=$user->getFavoris();
       // dd($favoris);
       $fournisseurs=[];
       if($favoris!=null){
           $i=0;
           while($i<count($favoris)){
               array_push($fournisseurs,$utilisateurRepository->findOneBy(['id'=>$favoris[$i]->getIdFournisseur()]));
               $i++;
           }
       }
      
        
        return $this->render('favoris/liste_favoris.html.twig', [
            'fournisseurs' => $fournisseurs,
            'user'=>$user
        ]);
    }
     /**
     * @IsGranted("ROLE_CLIENT",message="Seul un client peut accéder a ce contenu !  ")
     * @Route("/ajouter_favoris/{user}",name="app_favoris_add_fournisseur")
     */
    public function ajouterAuxFavoris(Utilisateur $user,EntityManagerInterface $entityManagerInterface,FlashyNotifier $flashy,FavorisRepository $favorisRepository){
        $favoris=$favorisRepository->findOneBy(['utilisateur'=>$this->getUser()->getId(),'idFournisseur'=>$user->getId()]);
        if($favoris==null){
            $favoris=new Favoris();
            $favoris->setUtilisateur($this->getUser());
            $favoris->setIdFournisseur($user->getId());
            $entityManagerInterface->persist($favoris);
            $entityManagerInterface->flush();
        }
        else{

        }
        return $this->redirectToRoute('app_consulter_fournisseur',[
            'user'=>$user->getId()
        ]);
    
    }
    
    /**
     * @IsGranted("ROLE_CLIENT",message="Seul un client peut accéder a ce contenu !  ")
     * @Route("/supprimer_favoris/{user}/{choix}",name="app_favoris_delete_fournisseur")
     */
    public function supprimerDeFavoris(Utilisateur $user,EntityManagerInterface $entityManagerInterface,FlashyNotifier $flashy,FavorisRepository $favorisRepository,$choix){
        // 
        $favoris=$favorisRepository->findOneBy(['utilisateur'=>$this->getUser()->getId(),'idFournisseur'=>$user->getId()]);
        if($favoris!=null){
            $mainUser=$this->getUser();
            $mainUser->removeFavori($favoris);
            $entityManagerInterface->flush();
        }
        if($choix==1){
            return $this->redirectToRoute('app_favoris_nav');
        }
        else{
            return $this->redirectToRoute('app_consulter_fournisseur',[
                'user'=>$user->getId()
            ]);
        }
        
     }
     /**
      * @Route("/favoris",name="app_favoris_nav")
      */
    public function naivgation(){

        return $this->redirectToRoute('app_consulter_favoris',['user'=>$this->getUser()->getId()]);
    }
     
 
 
 
     

}
