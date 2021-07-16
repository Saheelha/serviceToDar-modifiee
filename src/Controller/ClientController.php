<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\UpdateClientType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ClientController extends AbstractController
{
    /**
     * @IsGranted("ROLE_CLIENT",message="Vous devez être un client pour voir cette page ")
     * @Route("/client", name="app_client")
     */
    public function index(): Response
    {
        return $this->render('client/index.html.twig', [
            'controller_name' => 'ClientController',
        ]);
    }




    /**
     * @IsGranted("ROLE_CLIENT",message="Vous devez être un client pour voir cette page ")
     * @Route("/client/modifier_profil/{user}",name="app_update_client")
     */
    public function modifierClient(Utilisateur $user,Request $request,EntityManagerInterface $entityManager){
        //images_directory_user_profil
        $img=$user->getPhoto();
    $form=$this->createForm(UpdateClientType::class,$user);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
           //dd($form);
           //dd($img,$form,$user);
            $newfile=$form->get('image')->getData();
            if($newfile && $img!=null){
               try{
                unlink($this->getParameter('images_directory_user_profil').$img);
               }
               catch(\Exception $e){
                
               }
                ////
                
    }
    $image=$form->get('image')->getData();
           if($image){
           $fileName=md5(uniqid()).'.'.$image->guessExtension();
           $image->move(
           $this->getParameter('images_directory_user_profil'),$fileName);
           $user->setPhoto($fileName);
        }
    $entityManager->flush();

    return $this->redirectToRoute('app_accueil');
}
else{
    $img=$user->getPhoto();
}
       return $this->render('client/update_client.html.twig',[
            'form'=>$form->createView(),
            'user'=>$user
        ]);

    }

     /**
     * @Route("/consulter/{user}",name="app_admin_voir_client")
     */
    public function consulterClient(Utilisateur $user){
        return $this->render('client/voir_details.html.twig',[
            'user'=>$user
        ]);
    }
}
