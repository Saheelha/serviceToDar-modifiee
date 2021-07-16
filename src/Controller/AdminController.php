<?php

namespace App\Controller;

use DateTime;
use Exception;
use App\Entity\Utilisateur;
use App\Form\UpdateAdminType;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UtilisateurRepository;
use Symfony\Component\HttpFoundation\Request;
use MercurySeries\FlashyBundle\FlashyNotifier;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminController extends AbstractController
{
    /**
     * @IsGranted("ROLE_ADMIN",message="Vous devez être un client pour voir cette page ")
     * @Route("/admin", name="app_admin")
     */
    public function index(): Response
    {
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    /**
     * @IsGranted("ROLE_ADMIN",message="Vous devez être un admin pour voir cette page ")
     * @Route("admin/modifier_profil/{user}",name="app_update_admin")
     */
    public function updateAdmin(Utilisateur $user,Request $request, EntityManagerInterface $entityManager)
    {
        //images_directory_user_profil
        $img = $user->getPhoto();
        $form = $this->createForm(UpdateAdminType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            //dd($form);
            // dd($img,$form->get('image')->getData());
            $newfile = $form->get('image')->getData();
            if ($newfile && $img != null) {
                try{
                    unlink($this->getParameter('images_directory_user_profil') . $img);
                }
                catch(Exception $e){}
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

            return $this->redirectToRoute('app_accueil');
        } else {
            $img = $user->getPhoto();
        }
        return $this->render('admin/update_admin.html.twig', [
            'form' => $form->createView(),
            'user' => $user->getId()
        ]);
    }


    /**
     * @IsGranted("ROLE_ADMIN",message="Vous devez être un client pour voir cette page ")
     * @Route("/admin/liste_utilisateurs",name="app_admin_utilisateurs")
     */
    public function consulterUtilisateur(UtilisateurRepository $utilisateurRepository,EntityManagerInterface $entityManagerInterface)
    {
        $users = $utilisateurRepository->findAll();
        $this->controllerAbonnement($users,$entityManagerInterface);
        $utilisateurRepository->trouverClients();
        $u = [];
        $clients = [];
        $fournisseurs = [];
        $i = 0;
        while ($i < count($users)) {
            foreach ($users[$i]->getRoles() as $role) {
                # code..
                if ($role == 'ROLE_CLIENT') {
                    array_push($clients, $users[$i]);
                } else {
                    if ($role == 'ROLE_FOURNISSEUR') {
                        array_push($fournisseurs, $users[$i]);
                    }
                }
            }
            $i += 1;
        }
        return $this->render('admin/consulter_utilisateurs.html.twig', [
            'clients' => $clients,
            'fournisseurs' => $fournisseurs
        ]);
    }


    /**
     * @IsGranted("ROLE_ADMIN",message="Vous devez être un client pour voir cette page ")
     * @Route("/admin/bloquer_utilisateur/{user}",name="app_user_lock")
     */
    public function bloquerUtilisateur(Utilisateur $user, EntityManagerInterface $entityManagerInterface, FlashyNotifier $flashy, \Swift_Mailer $sender)
    {
        if ($user) {
            $user->setIsBlocked(true);
            $entityManagerInterface->flush();
            $messages = (new \Swift_Message('Blockage de votre compte '))
                ->setFrom('exemple@gmail.Com')
                ->setTo($user->getEmail())
                ->setBody(
                    $this->renderView(
                        // templates/emails/registration.html.twig
                        'mails/notifier_blockage.html.twig',
                        [
                            'username' => $user->getUsername(),
                            'dateBlockage'=>new DateTime('now'),
                            'email'=>'servicetodar@gmail.com'
                        ]
                    ),
                    'text/html'
                );
            $sender->send($messages);
            //$flashy->success('Le blocage est un succès ! ');
            return $this->redirectToRoute('app_admin_utilisateurs');
        } else {
           // $flashy->error('Echec aucun utilisateur n\'est trouvé !');
            return $this->redirectToRoute('app_admin_utilisateurs');
        }
    }


    /**
     * @IsGranted("ROLE_ADMIN",message="Vous devez être un client pour voir cette page ")
     * @Route("/admin/debloquer_utilisateur/{user}",name="app_user_unlock")
     */
    public function debloquerUtilisateur(Utilisateur $user, EntityManagerInterface $entityManagerInterface, FlashyNotifier $flashy, \Swift_Mailer $sender)
    {
        if ($user) {
            $user->setIsBlocked(false);
            $entityManagerInterface->flush();
            $messages = (new \Swift_Message('Déblockage de votre compte '))
                ->setFrom('exemple@gmail.Com')
                ->setTo($user->getEmail())
                ->setBody(
                    $this->renderView(
                        // templates/emails/registration.html.twig
                        'mails/notifier_deblockage.html.twig',
                        [
                            'username' => $user->getUsername(),
                            'dateDebloquage'=>new DateTime('now')
                        ]
                    ),
                    'text/html'
                );
            $sender->send($messages);
            ///$flashy->success('Le déblocage est un succès ! ');
            return $this->redirectToRoute('app_admin_utilisateurs');
        } else {
            //$flashy->error('Echec aucun utilisateur n\'est trouvé !');
            return $this->redirectToRoute('app_admin_utilisateurs');
        }
    }

   

    public function controllerAbonnement($users,EntityManagerInterface $entityManagerInterface){
        if(is_array($users)){
            foreach ($users as $user ) {
                # code...
                foreach ($user->getRoles() as $role) {
                    # code...
                    if($role=='ROLE_FOURNISSEUR'){
                        $date=new DateTime('now');
                $res=$date->diff($user->getDateExpiration());
                $res=(int) $res->format('%R%a');
                if($res<0){
                    $user->setAbonnement(false);
                }
                else{
                    $user->setAbonnement(true);
                }
                    }
                }

            }
            $entityManagerInterface->flush();
        }
    }
/**
 * @Route("/admin/consulter_clients",name="app_controller_clients")
 */
    public function controllerClients(UtilisateurRepository $utilisateurRepository){
        $clients=$utilisateurRepository->trouverClients();
        return $this->render("admin/consulter_clients.html.twig",
    [
        'clients'=>$clients
    ]);
    }

    /**
 * @Route("/admin/consulter_fournisseurs",name="app_controller_fournisseurs")
 */
    public function controllerFournisseurs(UtilisateurRepository $utilisateurRepository){
        $fournisseurs=$utilisateurRepository->trouverFournisseurs();
        return $this->render("admin/consulter_fournisseur.html.twig",[
            'fournisseurs'=>$fournisseurs
        ]);
    }

}
