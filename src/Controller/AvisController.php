<?php

namespace App\Controller;

use DateTime;
use App\Entity\Avis;
use App\Entity\Produit;
use App\Entity\Service;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AvisController extends AbstractController
{
    /**
     * @Route("/avis", name="avis")
     */
    public function index(): Response
    {
        return $this->render('avis/index.html.twig', [
            'controller_name' => 'AvisController',
        ]);
    }

    /**
     * @Route("/ajouter_avis/{produit}",name="app_avis_ajouter")
     */
    public function ajouterAvisProduit(Produit $produit, Request $request, EntityManagerInterface $entityManager)
    {
        $avis = new Avis();
        $s1 = $request->request->get('1_star');
        $s2 = $request->request->get('2_star');
        $s3 = $request->request->get('3_star');
        $s4 = $request->request->get('4_star');
        $s5 = $request->request->get('5_star');
        if ($s1 == '1') {
            # code...
            $avis->setNote(1);
        }
        if ($s2 == '2') {
            # code...
            $avis->setNote(2);
        }
        if ($s3 == '3') {
            # code...
            $avis->setNote(3);
        }
        if ($s4 == '4') {
            # code...
            $avis->setNote(4);
        }
        if ($s5 == '5') {
            # code...
            $avis->setNote(5);
        }
        $message = $request->request->get('message');
        $avis->setProduit($produit);
        $avis->setContenu($message);
        $avis->setUtilisateur($this->getUser());
        $avis->setCreatedAt(new DateTime('now'));
        $entityManager->persist($avis);
        $entityManager->flush();
        return $this->redirectToRoute('app_details_produit', [
            'produit' => $produit->getId()
        ]);
    }

    /**
     * @Route("/supprimer_avis/{avis}",name="app_supprimer_avis")
     */
    public function supprimerAvisProduit(Avis $avis, EntityManagerInterface $entityManager)
    {
        if ($avis != null) {
            # code...
            $produit = $avis->getProduit();
            $entityManager->remove($avis);
            $entityManager->flush();
            return $this->redirectToRoute('app_details_produit', [
                'produit' => $produit->getId()
            ]);
        }
    }

    /**
     * @IsGranted("ROLE_USER")
     * @Route("/exprimer_son_avis/produit/{produit}",name="app_user_avis")
     */
    public function exprimerAvis(Produit $produit)
    {
        return $this->redirectToRoute('app_details_produit', [
            'produit' => $produit->getId()
        ]);
    }


    /**
     * @Route("/ajouter/avis/service/{service}",name="app_avis_service_ajouter")
     */
    public function ajouterAvisService(Service $service, Request $request, EntityManagerInterface $entityManager)
    {
        $avis = new Avis();
        $s1 = $request->request->get('1_star');
        $s2 = $request->request->get('2_star');
        $s3 = $request->request->get('3_star');
        $s4 = $request->request->get('4_star');
        $s5 = $request->request->get('5_star');
        if ($s1 == '1') {
            # code...
            $avis->setNote(1);
        }
        if ($s2 == '2') {
            # code...
            $avis->setNote(2);
        }
        if ($s3 == '3') {
            # code...
            $avis->setNote(3);
        }
        if ($s4 == '4') {
            # code...
            $avis->setNote(4);
        }
        if ($s5 == '5') {
            # code...
            $avis->setNote(5);
        }
        $message = $request->request->get('message');
        $avis->setService($service);
        $avis->setContenu($message);
        $avis->setUtilisateur($this->getUser());
        $avis->setCreatedAt(new DateTime('now'));
        $entityManager->persist($avis);
        $entityManager->flush();
        return $this->redirectToRoute('app_service_details', [
            'service' => $service->getId()
        ]);
    }

    /**
     * @Route("/service/supprimer/{avis}",name="app_avis_service_delete")
     */
    public function supprimerAvisService(Avis $avis, EntityManagerInterface $entityManager)
    {
        if ($avis != null) {
            # code...
            $service = $avis->getService();
            $entityManager->remove($avis);
            $entityManager->flush();
            return $this->redirectToRoute('app_service_details', [
                'service' => $service->getId()
            ]);
        }
    }


     /**
      * @IsGranted("ROLE_USER")
     * @Route("/exprimer_son_avis/service/{service}",name="app_user_avis_service")
     */
    public function exprimerAvisService(Service $service)
    {
        return $this->redirectToRoute('app_service_details', [
            'service' => $service->getId()
        ]);
    }
}
