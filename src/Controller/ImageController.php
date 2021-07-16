<?php

namespace App\Controller;

use App\Entity\Image;
use App\Repository\ImageRepository;
use App\Repository\ProduitRepository;
use App\Repository\ServiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ImageController extends AbstractController
{
    /**
     * @Route("/image", name="image")
     */
    public function index(): Response
    {
        return $this->render('image/index.html.twig', [
            'controller_name' => 'ImageController',
        ]);
    }

    /**
     * @Route("/produit/supprimer_image/{image}",name="app_image_produit_delete")
     */
    public function supprimerImageProduit(Image $image, EntityManagerInterface $entityManager, ImageRepository $imageRepository, ProduitRepository $produitRepository,$res=null)
    {
        //  $img=$imageRepository->findOneBy(['id'=>$image]);
        //dd($id);
        $date=new \DateTime('now');
        if($this->getUser()->getDateExpiration()){
            $res=$date->diff($this->getUser()->getDateExpiration());
        $res=(int) $res->format('%R%a');
        }
        
        if ($image) {
            $id=$image->getProduit()->getId();
           try{
            unlink($this->getParameter('images_directory_produits') . $image->getTitre());
           }
           catch(\Exception $e){}
            finally{
                $entityManager->remove($image);
            $entityManager->flush();
            }
            foreach ($this->getUser()->getRoles() as $role) {
                # code...
                if($role=='ROLE_FOURNISSEUR'){
                    return $this->redirectToRoute('app_fournisseur_update_produit', ['produit' => $image->getProduit()->getId(),'res'=>$res]);
                }
                else{
                    if($role=='ROLE_ADMIN'){
                        return $this->redirectToRoute('app_details_produit',[
                            'produit'=>$image->getProduit()->getId()
                        ]);
                    }
                }
            }
            
        } else {
            return $this->redirectToRoute('app_fournisseur_produit');
        }
    }


    /**
     * @Route("/service/supprimer_images/{image}",name="app_image_service_delete")
     */
    public function supprimerImageService(Image $image ,EntityManagerInterface $entityManager, ImageRepository $imageRepository, ServiceRepository $produitRepository)
    {
        if ($image) {
           try {
            unlink($this->getParameter('images_directory_services') . $image->getTitre());
           } catch (\Exception $th) {
               //throw $th;
           }
            $entityManager->remove($image);
            $entityManager->flush();
            //return $this->redirectToRoute('app_update_service', ['service' => $image->getService()->getId()]);
            foreach ($this->getUser()->getRoles() as $role) {
                # code...
                if($role=='ROLE_FOURNISSEUR'){
                    return $this->redirectToRoute('app_update_service', ['service' => $image->getService()->getId()]);
                }
                else{
                    if($role=='ROLE_ADMIN'){
                        return $this->redirectToRoute('app_service_details',[
                            'service'=>$image->getService()->getId()
                        ]);
                    }
                }
            }
        } else {
            return $this->redirectToRoute('app_service_fournisseur');
        }
    }
}
