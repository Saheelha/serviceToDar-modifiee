<?php

namespace App\Controller;

use App\Entity\CategorieMetier;
use App\Entity\SousCategorie;
use App\Form\AjouterSousCategorieType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SousCategorieController extends AbstractController
{
    /**
     * @Route("/sous/categorie", name="sous_categorie")
     */
    public function index(): Response
    {
        return $this->render('sous_categorie/index.html.twig', [
            'controller_name' => 'SousCategorieController',
        ]);
    }


/**
 * @Route("/ajouter_sous_categorie/{categorie}",name="app_sous_categorie_ajout")
 */
    public function ajouterSousCategorie(Request $request,EntityManagerInterface $entityManager,CategorieMetier $categorie){
        $sousCategorie=new SousCategorie();
        $form=$this->createForm(AjouterSousCategorieType::class,$sousCategorie);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            # code...
            $sousCategorie->setCategorie($categorie);
            $entityManager->persist($sousCategorie);
            $entityManager->flush();
            return $this->redirectToRoute('app_categories_details',[
                'categorie'=>$categorie->getId()
            ]);
        }
        return $this->render('sous_categorie/ajouter_sous_categorie.html.twig',[
            'form'=>$form->createView()
        ]);
    }


    /**
     * @Route("/modifier_sous_categorie/{sousCategorie}",name="app_sous_categorie_modification")
     */
    public function modifierSousCategorie(Request $request,EntityManagerInterface $entityManager,SousCategorie $sousCategorie){
        $form=$this->createForm(AjouterSousCategorieType::class,$sousCategorie);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            # code...
            $entityManager->flush();
            return $this->redirectToRoute('app_categories_details',[
                'categorie'=>$sousCategorie->getCategorie()->getId()
            ]);
        }
        return $this->render('sous_categorie/modifier_sous_categorie.html.twig',[
            'form'=>$form->createView()
        ]);
    }



    /**
     * @Route("/supprimer_sous_categorie/{sousCategorie}",name="app_sous_categorie_suppression")
     */
    public function supprimerSousCategorie(SousCategorie $sousCategorie,EntityManagerInterface $entityManager){
        if ($sousCategorie!=null) {
            try {
                $entityManager->remove($sousCategorie);
            $entityManager->flush();
            } catch (\Exception $th) {
                //throw $th;
            }
            # code...
        }
        return $this->redirectToRoute('app_categories_details',[
            'categorie'=>$sousCategorie->getCategorie()->getId()
        ]);
    }


    /**
     * @Route("/sous_categorie/details/{sousCategorie}",name="app_sous_categorie_show")
     */
    public function voirDetailsSousCatégorie(SousCategorie $sousCategorie){
        if($sousCategorie!=null){
            return $this->render('sous_categorie/voir_details.html.twig',[
                'sousCategorie'=>$sousCategorie
            ]);
        }
        else{
            return $this->redirectToRoute('app_categories_details',[
                'categorie'=>$sousCategorie->getCategorie()->getId()
            ]);
        }
    }
}
