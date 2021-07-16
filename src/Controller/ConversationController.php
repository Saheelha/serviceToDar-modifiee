<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Entity\Conversation;
use App\Entity\Message;
use App\Form\EnvoiMessageType;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ConversationRepository;
use DateTime;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class ConversationController extends AbstractController
{
    /**
     * @Route("/conversation", name="conversation")
     */
    public function index(): Response
    {
        return $this->render('conversation/index.html.twig', [
            'controller_name' => 'ConversationController',
        ]);
    }





    /**
     * @IsGranted("ROLE_USER",message="Seule les utilisateur peuvent tchatter avec un fournisseur ")
     * @Route("/consulter_mes_message/{otherUser}/{secondUser}",name="app_consulter_conversation")
     */
    public function voirDetailsConversation(Utilisateur $otherUser,Utilisateur $secondUser=null, EntityManagerInterface $entityManagerInterface, Request $request, ConversationRepository $conversationRepository, $res = null)
    {
        $user = $this->getUser();
        foreach ($user->getRoles() as $role) {
            if ($role == "ROLE_CLIENT" || $role == "ROLE_FOURNISSEUR") {
                if ($user->getDateExpiration()) {
                    $date = new \DateTime('now');
                    $res = $date->diff($user->getDateExpiration());
                    $res = (int) $res->format('%R%a');
                }
                if ($otherUser) {
                    $conversation = $conversationRepository->trouverConversation($user,$otherUser);
                    if (is_null($conversation)==true) {
                        $this->ajouterConversation($user, $otherUser, $entityManagerInterface);
                        $conversation=$conversationRepository->trouverConversation($user,$otherUser);
                    }
                    $message = new Message();
                    $message->setUtilisateur($user);
                    $message->setConversation($conversation);
                    $form = $this->createForm(EnvoiMessageType::class);
                    $form->handleRequest($request);
                    if ($form->isSubmitted() && $form->isValid()) {
                        $contenu = $form->get('contenu')->getData();
                        $this->envoyerMessage($user, $conversation, $entityManagerInterface, $contenu);
                        return $this->redirectToRoute('app_consulter_conversation', [
                            'otherUser' => $otherUser->getId()
                        ]);
                    }
                    if ($res < 0) {
                        $envoyerMessage = false;
                    } else {
                        $envoyerMessage = true;
                    }
                    //dd($envoyerMessage);
                    return $this->render('conversation/consulter_mes_conversation.html.twig', [
                        'messages' => $conversation->getMessages(),
                        'user' => $user,
                        'autherUser' => $otherUser,
                        'form' => $form->createView(),
                        'res' => $res,
                        'envoyerMessage' => $envoyerMessage
                    ]);
                } else {
                    return $this->redirectToRoute('app_consulter_fournisseur', [
                        'user' => $otherUser,
                        'res' => $res
                    ]);
                }
            } else {
                if ($role == "ROLE_ADMIN") {
                    $conversation=$conversationRepository->trouverConversation($otherUser,$secondUser);
                    // $conversation = null;
                    // $conversations = $otherUser->getConversations();
                    // foreach ($conversations as $c) {
                    //     # code...
                    //     foreach ($c->getUtilisateurs() as $u) {
                    //         # code...
                    //         if ($u->getId() == $otherUser->getId()) {
                    //             $conversation = $c;
                    //             break;
                    //         }
                    //     }
                    // }
                    // $userOne = $conversation->getUtilisateurs()[0];
                    // $userTwo = $conversation->getUtilisateurs()[1];
                    return $this->render('conversation/voir_conversation_entre_utilisateurs.html.twig', [
                        'messages' => $conversation->getMessages(),
                        'mainUser' => $otherUser
                    ]);
                }
            }
            # code...
        }
    }

    /**
     *  @IsGranted("ROLE_USER",message="Seule les utilisateur peuvent tchatter avec un fournisseur ")
     * @Route("/mes_conversations/{user}",name="app_mes_conversations")
     */
    public function consulterConversations(Utilisateur $user, ConversationRepository $conversationRepository)
    {
        //$user=$this->getUser();
        foreach ($this->getUser()->getRoles() as $role) {
            # code...
            if ($role == "ROLE_CLIENT" || $role == "ROLE_FOURNISSEUR") {
                $envoyerMessage = true;
                $res = 0;
                if ($user->getDateExpiration()) {
                    $date = new \DateTime('now');
                    $res = $date->diff($this->getUser()->getDateExpiration());
                    $res = (int) $res->format('%R%a');
                    if ($res < 0) {
                        $envoyerMessage = false;
                    }
                }
                //$conversations=$user->getConversations();
                $conversations = $conversationRepository->findConversationsByLastMessage($user);
                return $this->render('conversation/voir_mes_conversations.html.twig', [
                    'conversations' => $conversations, 'res' => $res, 'envoyerMessage' => $envoyerMessage
                ]);
            } else {
                if ($role == "ROLE_ADMIN") {
                    $conversations = $user->getConversations();
                    $conversation = $conversationRepository->findConversationsByLastMessage($user);
                    //dd($conversation);
                    return $this->render('conversation/voir_les_conversations_utilisateur.html.twig', [
                        'conversations' => $conversations,
                        'mainUser' => $user
                    ]);
                }
            }
        }
    }

    public function ajouterConversation(Utilisateur $user, Utilisateur $otherUser, EntityManagerInterface $entityManagerInterface)
    {
        $conversation = new Conversation();
        $conversation->addUtilisateur($user);
        $conversation->addUtilisateur($otherUser);
        $conversation->setTitre('Conversation entre ' . $user->getUsername() . ' et ' . $otherUser->getUsername());
        $entityManagerInterface->persist($conversation);
        $entityManagerInterface->flush();
        //return $conver
    }

    public function envoyerMessage(Utilisateur $user, Conversation $conversation, EntityManagerInterface $entityManagerInterface, $contenu)
    {
        $message = new Message();
        $message->setSendAt(new DateTime('now'));
        $message->setUtilisateur($user);
        $message->setContenu($contenu);
        $conversation->setDateDernierMessage($message->getSendAt());
        $conversation->addMessage($message);
        $entityManagerInterface->persist($conversation);
        $entityManagerInterface->flush();
    }
}
