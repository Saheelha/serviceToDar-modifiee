<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\UpdateFournisseurType;
use App\Repository\FavorisRepository;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class FournisseurController extends AbstractController
{
    /**
     * @IsGranted("ROLE_FOURNISSEUR",message="Vous devez être un client pour voir cette page ")
     * @Route("/fournisseur", name="app_fournisseur")
     */
    public function index(): Response
    {
        return $this->render('fournisseur/index.html.twig', [
            'controller_name' => 'FournisseurController',
        ]);
    }

    /**
     * @IsGranted("ROLE_FOURNISSEUR",message="Vous devez être un fournisseur pour voir cette page ")
     * @Route("/fournisseur/modifier_profil/{user}",name="app_update_fournisseur")
     */
    public function modifierFournisseur(Utilisateur $user,Request $request, EntityManagerInterface $entityManager,$res=null)
    {
        //images_directory_user_profil
        $date=new \DateTime('now');
        $date=new \DateTime('now');
        if($user->getDateExpiration()!=null){
            $res=$date->diff($this->getUser()->getDateExpiration());
            $res=(int) $res->format('%R%a');
        }
        $img = $user->getPhoto();
        $form = $this->createForm(UpdateFournisseurType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            //dd($form);
            //dd($img,$form,$user);
            $newfile = $form->get('image')->getData();
            if ($newfile && $img != null) {
                try{
                    unlink($this->getParameter('images_directory_user_profil') . $img);
                }
                catch(\Exception $e){}
                ////

            }
            $image = $form->get('image')->getData();
            if ($image) {
                $fileName = md5(uniqid()) . '.' . $image->guessExtension();
                $image->move(
                    $this->getParameter('images_directory_user_profil'),
                    $fileName
                );
                $user->setPhoto($fileName);
            }
            $entityManager->flush();

            return $this->redirectToRoute('app_accueil',['$res'=>$res]);
        } else {
            $img = $user->getPhoto();
        }
        return $this->render('fournisseur/update_fournisseur.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
            'res'=>$res
        ]);
    }


    /**
     * @Route("/consulter_fournisseur/{user}",name="app_consulter_fournisseur")
     */
    public function consulterFournisseur(Utilisateur $user,UtilisateurRepository $utilisateurRepository,FavorisRepository $favorisRepository,$res=null){
      
        if($this->getUser()!=null){
            //$favoris=$this->getUser()->getFavoris();
            $favoris=$favorisRepository->findOneBy(['utilisateur'=>$this->getUser()->getId(),'idFournisseur'=>$user->getId()]);
        $inclu=false;
        if($favoris!=null){
            $inclu=true;
        }
        $produits=$user->getProduits();
        $services=$user->getServices();
        return $this->render('fournisseur/consulter_fournisseur.html.twig',[
            'produits'=>$produits,
            'services'=>$services,
            'user'=>$user,
            'trouve'=>$inclu
        ]);
        }
        else{
        $inclu=false;
        $produits=$user->getProduits();
        $services=$user->getServices();
        return $this->render('fournisseur/consulter_fournisseur.html.twig',[
            'produits'=>$produits,
            'services'=>$services,
            'user'=>$user,
            'trouve'=>$inclu
        ]);
        }
    }
    
    
}
