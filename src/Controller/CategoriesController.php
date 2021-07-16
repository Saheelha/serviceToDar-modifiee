<?php

namespace App\Controller;

use Exception;
use App\Entity\CategorieMetier;
use App\Form\UpdateCategorieType;
use App\Form\AjouterCategorieType;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\CategorieMetierRepository;
use Symfony\Component\HttpFoundation\Request;
use MercurySeries\FlashyBundle\FlashyNotifier;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CategoriesController extends AbstractController
{
    /**
     * 
     * @Route("/admin/categories", name="app_categories_admin")
     */
    public function consulterCategoriesMetiers(CategorieMetierRepository $categorieMetierRepository): Response
    {
        if($this->getUser()!=null){
            foreach ($this->getUser()->getRoles() as $role) {
                # code...
                if($role=='ROLE_ADMIN'){
                    $categories = $categorieMetierRepository->findAll();
        return $this->render('categories/liste_categories.html.twig', [
            'categories' => $categories
        ]);
                }
            }
        }
        else{
            return $this->redirectToRoute('app_accueil');
        }
    }


    /**
     * @IsGranted("ROLE_ADMIN",message="Seule un administrateur peut consulter cette page  ! ")
     * @Route("/admin/ajouter_categorie",name="app_categorie_add")
     */
    public function ajouterCategorieMetier(Request $request, EntityManagerInterface $entityManagerInterface)
    {
        $categorie = new CategorieMetier();
        $form = $this->createForm(AjouterCategorieType::class, $categorie);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $image = $form->get('icone')->getData();
            if ($image) {

                $fileName = md5(uniqid()) . '.' . $image->guessExtension();
               // dd($image,$fileName);
                $image->move(
                    $this->getParameter('images_directory_categories'),
                    $fileName
                );
                $categorie->setIcone($fileName);
            }
            $entityManagerInterface->persist($categorie);
            $entityManagerInterface->flush();
            return $this->redirectToRoute('app_categories_admin');
        }
        return $this->render('categories/ajouter_categorie.html.twig', [
            'form' => $form->createView()
        ]);
    }



    /**
     * @IsGranted("ROLE_ADMIN",message="Seule un administrateur peut consulter cette page  ! ")
     * @Route("/admin/modifier_categorie/{categorie}",name="app_categorie_update")
     */
    public function modifierCategorieMetier(CategorieMetier $categorie, Request $request, EntityManagerInterface $entityManagerInterface)
    {
        $form = $this->createForm(UpdateCategorieType::class, $categorie);
        $form->handleRequest($request);
        $img = $categorie->getIcone();
        if ($form->isSubmitted() && $form->isValid()) {
            $newfile = $form->get('image')->getData();
            if ($newfile) {
                if ($img != null) {
                   try {
                    unlink($this->getParameter('images_directory_categories') . $img);
                   } catch (\Exception $th) {
                       //throw $th;
                   }
                }
                $fileName = md5(uniqid()) . '.' . $newfile->guessExtension();
                $newfile->move(
                    $this->getParameter('images_directory_categories'),
                    $fileName
                );
                $categorie->setIcone($fileName);
            }
            $entityManagerInterface->flush();
            return $this->redirectToRoute('app_categories_admin');
        }
        return $this->render('categories/update_categorie.html.twig', [
            'form' => $form->createView(),
            'categorie' => $categorie
        ]);
    }


    /**
     * @IsGranted("ROLE_ADMIN",message="Seule un administrateur peut consulter cette page  ! ")
     * @Route("/admin/supprimer_categorie/{categorie}",name="app_categorie_delete")
     */
    public function supprimerCategorieMetier(CategorieMetier $categorie, EntityManagerInterface $entityManagerInterface, FlashyNotifier $flashy)
    {
        if ($categorie) {
            $image = $categorie->getIcone();
            if ($image != null) {
               try{
                unlink($this->getParameter('images_directory_categories') . $image);
               }
               catch(Exception $e){}
            }
            try {
                //code...
                $entityManagerInterface->remove($categorie);
            $entityManagerInterface->flush();
           // $flashy->success("La catégorie a été supprimé avec succès !");
            } catch (\Exception $e) {
                //throw $th;
             //   $flashy->error("La catégorie que vous voulez supprimé a des utilisateur classé selon elle ! ");
                return $this->redirectToRoute('app_categories_admin');
            }
            return $this->redirectToRoute('app_categories_admin');
        } else {
            //$flashy->error('Catégorie inexistante  !');
            return $this->redirectToRoute('app_categories_admin');
        }
    }


    /**
     * 
     * @Route("/voir_details/{categorie}",name="app_categories_details")
     */
    public function voirDetailsCategorieMetier(CategorieMetier $categorie){
        if($this->getUser()!=null){
            foreach ($this->getUser()->getRoles() as $role) {
                # code...
                if ($role=='ROLE_ADMIN') {
                    # code...
                    return $this->render('categories/details_categorie_admin.html.twig',[
                        'categorie'=>$categorie
                    ]);
                }
                else {
                    return $this->render('categories/details_categorie.html.twig',[
                        'categorie'=>$categorie
                    ]);
                   
                    }
                }
        }
        else{
            return $this->render('categories/details_categorie.html.twig',[
                'categorie'=>$categorie
            ]);
        }
        
        
    }
}
