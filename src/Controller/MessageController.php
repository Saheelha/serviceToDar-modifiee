<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\Message;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MessageController extends AbstractController
{
    /**
     * @Route("/message", name="message")
     */
    public function index(): Response
    {
        return $this->render('message/index.html.twig', [
            'controller_name' => 'MessageController',
        ]);
    }


    /**
     * @Route("/admin/supprimer_message/{message}",name="app_admin_supprimer_message")
     */
    public function supprimerMessage(Message $message,EntityManagerInterface $entityManager){
        $conversation=$message->getConversation();
        $conversation->removeMessage($message);
        $entityManager->flush();
        if($this->getUser()->getDateExpiration()){
            $date=new \DateTime('now');
        $res=$date->diff($this->getUser()->getDateExpiration());
        $res=(int) $res->format('%R%a');
        }
        else{
            $res=null;
        }
        $sUser=null;
        foreach ($conversation->getUtilisateurs() as $u) {
            # code...
            if($u->getId()!= $message->getUtilisateur()->getId()){
                $sUser=$u;
            }
        }
       
        return $this->redirectToRoute('app_consulter_conversation',[
            'otherUser'=>$conversation->getUtilisateurs()[1]->getId(),
            'secondUser'=>$conversation->getUtilisateurs()[0]->getId()
        ]);
    }
}
