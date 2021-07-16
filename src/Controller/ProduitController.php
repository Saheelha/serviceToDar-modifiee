<?php

namespace App\Controller;

use App\Entity\AncientProduit;
use App\Entity\Avis;
use App\Entity\Image;
use App\Entity\Produit;
use App\Entity\Utilisateur;
use App\Form\AjouterAvisType;
use App\Form\AjouterProduitType;
use App\Repository\ProduitRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProduitController extends AbstractController
{
    /**
     *  @IsGranted("ROLE_FOURNISSEUR",message="Seul un fournisseur peut accéder a ce contenu !  ")
     * @Route("fournisseur/mes_produit/{user}", name="app_fournisseur_produit")
     */
    public function ConsulterProduits(Utilisateur $user): Response
    {
        if($user){
            if($user->getDateExpiration()!=null){
                $date=new \DateTime('now');
            $res=$date->diff($this->getUser()->getDateExpiration());
            $res=(int) $res->format('%R%a');
            $produits = $user->getProduits();
            return $this->render('produit/mes_produits.html.twig', [
                'produits' => $produits,
                'res'=>$res
            ]);
            }
        }
    }


    /**
     *  @IsGranted("ROLE_FOURNISSEUR",message="Seule les fournisseur peuvent ajouter un produit ! ")
     * @Route("/fournisseur/ajouter_produit/{res}",name="app_fournisseur_add_produit")
     */
    public function ajouterProduit(Request $request, EntityManagerInterface $entityManager,$res=null)
    {
        $produit = new Produit();
        
        $form = $this->createForm(AjouterProduitType::class, $produit);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            //$entityManager->persist($produit);
            $newfiles = $form->get('images')->getData();
           $this->ajouterImageProduit($produit,$newfiles);
            $produit->setUtilisateur($this->getUser());
            $monLien = $form->get('lienVideo')->getData();
            if (strpos($monLien, '/embed') == false) {
                $lien = str_replace('https://youtu.be/', 'https://www.youtube.com/embed/', $monLien, $i);
                if ($i == 0) {
                    $lien = str_replace('https://youtube.com/', 'https://www.youtube.com/embed/', $monLien, $i);
                }
                //    dd($monLien,$lien);
                $produit->setLienVideo($lien);
            } else {
                $produit->setLienVideo($monLien);
            }
            $produit->setRefProd(md5(uniqid()));
            if($produit->getPoids()==null){
                $produit->setPoids(0);
            }
            $entityManager->persist($produit);
            $entityManager->flush();
            return $this->redirectToRoute('app_fournisseur_produit',['user'=>$this->getUser()->getId()]);
        }

        return $this->render('produit/ajouter_produit.html.twig', [
            'form' => $form->createView(),
            'res'=>$res
        ]);
    }


    /**
     *  @IsGranted("ROLE_FOURNISSEUR",message="Seule les fournisseur peuvent ajouter un produit ! ")
     * @Route("/fournisseur/modifier/{produit}/{res}",name="app_fournisseur_update_produit")
     */
    public function modifierProduit(Produit $produit, Request $request, EntityManagerInterface $entityManager,$res=null)
    {
        $form = $this->createForm(AjouterProduitType::class, $produit);
        
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $newfiles = $form->get('images')->getData();
            $this->ajouterImageProduit($produit,$newfiles);
            $produit->setUtilisateur($this->getUser());
            $monLien = $form->get('lienVideo')->getData();
            if (strpos($monLien, '/embed') == false) {
                $lien = str_replace('https://youtu.be/', 'https://www.youtube.com/embed/', $monLien, $i);
                if ($i == 0) {
                    $lien = str_replace('https://youtube.com/', 'https://www.youtube.com/embed/', $monLien, $i);
                }
                //    dd($monLien,$lien);
                $produit->setLienVideo($lien);
            } else {
                $produit->setLienVideo($monLien);
            }
            //$dateLivraison=$form->get('dateLivraisons')->getData();
           //$produit->setDateLivraison(new DateTime($dateLivraison));
           if($produit->getRefProd()==null || $produit->getRefProd()==''){
               $produit->setRefProd(md5(uniqid()));
           } 
           $entityManager->persist($produit);
            $entityManager->flush();
            return $this->redirectToRoute('app_fournisseur_produit',['user'=>$this->getUser()->getId()]);
        }
        return $this->render('produit/update_produit.html.twig', [
            'form' => $form->createView(),
            'produit' => $produit,
            'res'=>$res
        ]);
    }


   

    /**
     * @Route("/produit/details/{produit}/{res}",name="app_details_produit")
     */
    public function consulterDetailsProduit(Produit $produit,$res=null)
    {
        if ($produit != null) {
            # code...
            if ($this->getUser() != null) {

                foreach ($this->getUser()->getRoles() as $role) {
                    # code...
                    if ($role == 'ROLE_FOURNISSEUR') {
                        return  $this->render('produit/details_produit_fournisseur.html.twig', [
                            'produit' => $produit,
                            'res'=>$res
                        ]);
                    }
                }
            }
            return  $this->render('produit/details_produit.html.twig', [
                'produit' => $produit
            ]);
        }
    }


    /**
     * @Route("/supprimer_produit/produit/{produit}",name="app_produit_delete")
     */
    public function supprimerProduit(Produit $produit,EntityManagerInterface $entityManager){
        $user=$produit->getUtilisateur();
        $images=$produit->getImages();
        foreach ($images as $image) {
            try {
                unlink($this->getParameter('images_directory_produits') . $image->getTitre());   
            
            } catch (\Throwable $th) {
                //throw $th;
            } 
            finally{
                $produit->removeImage($image);
            }        # code...
        }
        $ancientProduit=new AncientProduit();
        $ancientProduit->setNom($produit->getNom());
        $ancientProduit->setPrix($produit->getPrix());
        $ancientProduit->setPrixLivraison($produit->getPrixLivraison());
        $ancientProduit->setDateLivraison($produit->getDateLivraison());
        $ancientProduit->setUtilisateur($produit->getUtilisateur());
        $ancientProduit->setRefProd($produit->getRefProd());
        //$ancientProduit->setCommandeProduit($produit->getCommandeProduits());
        $ancientProduit->setQuantite($produit->getQuantite());
        
        //$entityManager->flush();

        foreach ($produit->getCommandeProduits() as $commandeProduit) {
            # code...
            //$commande =$commandeProduit->getCommande();
            $commandeProduit->setProduit(null);
            $ancientProduit->addCommandeProduit($commandeProduit);
            //$commande->removeCommandeProduit($commandeProduit);
        }
        foreach ($produit->getAvis() as $avis) {
            # code...
            $produit->removeAvi($avis);
        }
        $user->removeProduit($produit);
        $entityManager->persist($ancientProduit);
        $entityManager->flush();
        foreach ($this->getUser()->getRoles() as $role) {
            # code...
            if($role=='ROLE_FOURNISSEUR'){
                return $this->redirectToRoute('app_fournisseur_produit',['user'=>$this->getUser()->getId()]);
            }
            if($role=='ROLE_ADMIN'){
                return $this->redirectToRoute('app_consulter_fournisseur',[
                    'user'=>$user->getId(),
                    
                ]);
            }
        }
    }


    /**
     * @Route("/produit/supprimer_video/{produit}",name="app_produit_video")
     */
    public function supprimerVideo(Produit $produit,EntityManagerInterface $entityManagerInterface){
        $produit->setLienVideo('');
        $entityManagerInterface->flush();
        return $this->redirectToRoute('app_details_produit',[
            'produit'=>$produit->getId()
        ]);
    }

    public function ajouterImageProduit(Produit $produit,$newfiles){
        if (is_array($newfiles)) {
            foreach ($newfiles as $newfile) {
                # code...
                $fileName = md5(uniqid()) . '.' . $newfile->guessExtension();
                $newfile->move(
                    $this->getParameter('images_directory_produits'),
                    $fileName
                );
                $image = new Image();
                $image->setTitre($fileName);
                $image->setUploadedAt(new DateTime('now'));
                $image->setProduit($produit);
                // $entityManager
                $produit->addImage($image);
            }
        } else {
            $fileName = md5(uniqid()) . '.' . $newfiles->guessExtension();
            $newfiles->move(
                $this->getParameter('images_directory_produits'),
                $fileName
            );
            $image = new Image();
            $image->setTitre($fileName);
            $image->setUploadedAt(new DateTime('now'));
            $image->setProduit($produit);
            // $entityManager
            $produit->addImage($image);
        }
    }
}
