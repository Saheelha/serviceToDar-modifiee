<?php

namespace App\Controller;

use App\Entity\AncientService;
use DateTime;
use App\Entity\Image;
use App\Entity\Service;
use App\Entity\Utilisateur;
use App\Form\AjouterServiceType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ServiceController extends AbstractController
{
    /**
     * @IsGranted("ROLE_FOURNISSEUR",message="Seul un fournisseur peut accéder a ce contenu !  ")
     * @Route("fournisseur/mes_service/{user}", name="app_service_fournisseur")
     */
    public function consulterServices(Utilisateur $user): Response
    {
        if($user){
            if($user->getDateExpiration()!=null){
                $date=new \DateTime('now');
            $res=$date->diff($this->getUser()->getDateExpiration());
            $res=(int) $res->format('%R%a');
            }
        }
        $services = $this->getUser()->getServices();
        return $this->render('service/index.html.twig', [
            'services' => $services,
            'res'=>$res
        ]);
    }


    /**
     * @Route("details_service/{service}/{res}",name="app_service_details")
     */
    public function consulterDetailsService(Service $service,$res=null)
    {
        if ($service != null) {
            if ($this->getUser() != null) {
                # code...
                foreach ($this->getUser()->getRoles() as $role) {
                    # code...
                    if ($role == 'ROLE_FOURNISSEUR') {
                        return $this->render('service/voir_service_details_fournisseur.html.twig', [
                            'service' => $service,
                            'res'=>$res
                        ]);
                    }
                }
            }
            return $this->render('service/voir_service_details.html.twig', [
                'service' => $service
            ]);
        } else {
            return $this->redirectToRoute('app_service_fournisseur',['user'=>$this->getUser()->getId()]);
        }
    }

    /**
     * @IsGranted("ROLE_FOURNISSEUR",message="Seul un fournisseur peut accéder a ce contenu !  ")
     * @Route("/ajouter_service/{res}",name="app_sevice_add")
     */
    public function ajouterService(Request $request, EntityManagerInterface $entityManager,$res=null)
    {
        $service = new Service();
        $form = $this->createForm(AjouterServiceType::class, $service);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            //$entityManager->persist($produit);
            $newfiles = $form->get('images')->getData();
            $this->ajouterImageService($service,$newfiles);
               
            $ch=$service->getNom();
            while(strpos("'", $ch)==true){
                $ch=str_replace("'","\'",$ch);
            }
           // $service->setNom($ch);
            $service->setUtilisateur($this->getUser());
            $link = $form->get('lienVideo')->getData();
            if ($link != null) {
                if (strpos($link, '/embed') == false) {
                    $lien = str_replace('https://youtu.be/', 'https://www.youtube.com/embed/', $link, $i);
                    if ($i == 0) {
                        $lien = str_replace('https://youtube.com/', 'https://www.youtube.com/embed/', $link, $i);
                    }
                    //    dd($monLien,$lien);
                    $service->setLienVideo($lien);
                } else {
                    $service->setLienVideo($link);
                }
            }
            $service->setRefService(md5(uniqid()));
            $entityManager->persist($service);
            $entityManager->flush();
            return $this->redirectToRoute('app_service_fournisseur',['user'=>$this->getUser()->getId()]);
        }

        return $this->render('service/ajouter_service.html.twig', [
            'form' => $form->createView(),
            'res'=>$res
        ]);
    }



    /**
     * @Route("/supprimer_service/{service}",name="app_service_delete")
     */
    public function supprimerService(Service $service, EntityManagerInterface $entityManager)
    {
        if ($service != null) {
            $images = $service->getImages();
            foreach ($images as $image) {
               try {
                unlink($this->getParameter('images_directory_services') . $image->getTitre());  
               } catch (\Exception $th) {
                   //throw $th;
               }  
               finally{
                $service->removeImage($image);
               }      # code...
            }
            $ancientService=new AncientService();
            $ancientService->setNom($service->getNom());
            $ancientService->setPrix($service->getPrix());
            $ancientService->setDateReservation($service->getDateReservation());
            $ancientService->setRefService($service->getRefService());
            $ancientService->setUtilisateur($service->getUtilisateur());
            foreach ($service->getAvis() as $avis) {
                # code...
                $service->removeAvi($avis);
            }
            foreach ($service->getCommandeService() as $commandeService) {
                # code...
               // $commande=$commandeService->getCommande();
               $commandeService->setService(null);
               $ancientService->addCommandeService($commandeService);
                //$commande->removeServiceCommande($commandeService);
            }
            $user=$service->getUtilisateur();
            $user->removeService($service);
            $entityManager->persist($ancientService);
            $entityManager->flush();
            foreach ($this->getUser()->getRoles() as $role) {
                # code...
                if($role=='ROLE_FOURNISSEUR'){
                    return $this->redirectToRoute('app_service_fournisseur',['user'=>$this->getUser()->getId()]);
                }
                if($role=='ROLE_ADMIN'){
                    return $this->redirectToRoute('app_consulter_fournisseur',[
                        'user'=>$user->getId()
                    ]);
                }
            }
        }
        return $this->redirectToRoute('app_service_fournisseur',['user'=>$this->getUser()->getId()]);
    }



    /**
     * @IsGranted("ROLE_FOURNISSEUR",message="Seul un fournisseur peut accéder a ce contenu !  ")
     * @Route("/modifier_service/{service}/{res}",name="app_update_service")
     */
    public function modifierService(Service $service, Request $request, EntityManagerInterface $entityManager,$res=null)
    {
        $form = $this->createForm(AjouterServiceType::class, $service);
        $form->handleRequest($request);
        //$form->get('lienVideo')->setData($service->getLienVideo());
        if ($form->isSubmitted() && $form->isValid()) {
            # code...
            $newfiles = $form->get('images')->getData();
            if ($newfiles == null) {
                # code...
            } else {
                // Si L'utilisateur a choisie plusieurs images
                $this->ajouterImageService($service,$newfiles);
            }
            $link = $form->get('lienVideo')->getData();
            if ($link != null) {
                if (strpos($link, '/embed') == false) {
                    $lien = str_replace('https://youtu.be/', 'https://www.youtube.com/embed/', $link, $i);
                    if ($i == 0) {
                        $lien = str_replace('https://youtube.com/', 'https://www.youtube.com/embed/', $link, $i);
                    }
                    //    dd($monLien,$lien);
                    $service->setLienVideo($lien);
                } else {
                    $service->setLienVideo($link);
                }
            }
            if($service->getRefService()==null || $service->getRefService()==''){
                $service->setRefService(md5(uniqid()));
            }
            $entityManager->persist($service);
            $entityManager->flush();
            return $this->redirectToRoute('app_service_fournisseur',['user'=>$this->getUser()->getId()]);
        }
        return $this->render('service/update_service.html.twig', [
            'form' => $form->createView(),
            'service' => $service,
            'res'=>$res
        ]);
    }

    /**
     * @Route("/service/supprimer_video/{service}",name="app_service_video")
     */
    public function supprimerVideo(Service $service,EntityManagerInterface $entityManagerInterface){
        $service->setLienVideo('');
        $entityManagerInterface->flush();
        return $this->redirectToRoute('app_service_details',[
            'service'=>$service->getId()
        ]);
    }


    public function ajouterImageService(Service $service,$newfiles){
        if (is_array($newfiles)) {
            foreach ($newfiles as $newfile) {
                # code...
                $fileName = md5(uniqid()) . '.' . $newfile->guessExtension();
                $newfile->move(
                    $this->getParameter('images_directory_services'),
                    $fileName
                );
                $image = new Image();
                $image->setTitre($fileName);
                $image->setUploadedAt(new DateTime('now'));
                $image->setService($service);
                // $entityManager
                $service->addImage($image);
            }
        } else {
            $fileName = md5(uniqid()) . '.' . $newfiles->guessExtension();
            $newfiles->move(
                $this->getParameter('images_directory_services'),
                $fileName
            );
            $image = new Image();
            $image->setTitre($fileName);
            $image->setUploadedAt(new DateTime('now'));
            $image->setService($service);
            // $entityManager
            $service->addImage($image);
        }
        
    }
}
