<?php

namespace App\Controller;

use DateTime;
use DateInterval;
use Swift_Mailer;
use Stripe\Stripe;
use App\Entity\Commande;
use Stripe\PaymentIntent;
use App\Entity\Utilisateur;
use App\Entity\CategorieMetier;
use App\Form\NousContacterType;
use App\Repository\CommandeRepository;
use App\Form\RechercheFournisseursType;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\Boolean;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use App\Repository\CategorieMetierRepository;
use Symfony\Component\HttpFoundation\Request;

use MercurySeries\FlashyBundle\FlashyNotifier;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class NavBarController extends AbstractController
{

    /**
     * Ici on gérer la navigation entre les pages principales 
     */


    /**
     * @Route("/",name="app_index")
     */
    public function index()
    {
        return $this->redirectToRoute('app_accueil');
    }

    /**
     * @Route("/accueil/{valider}/{erreur}",name="app_accueil")
     */
    public function accueil(CategorieMetierRepository $categorieMetierRepository,  Request $request,FlashyNotifier $flashy,Swift_Mailer $mail,CommandeRepository $commandeRepository,EntityManagerInterface $entityManagerInterface,$res=null,$valider=null,$erreur=null)
    {
        $images = [];
        $form = $this->createForm(NousContacterType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();
            $name = $form->get('nom')->getData();
            $message = $form->get('message')->getData();
            // envoie de mail avec mailer 
            // $mail = (new TemplatedEmail())
            //     ->from($email)
            //     ->to('exemple@gmail.com')
            //     ->subject('Nous contacter')
            //     ->htmlTemplate('mails/nous_contacter.html.twig')
            //     ->context([
            //         'username' => $name,
            //         'message' => $message
            //     ]);
            // $mailer->send($mail);

            //envoie de mail avec php native juste pour tester l'envoi de mail 
          //  $this->envoiDeMail($email,'dd609758@gmail.com',"Nous contacter",$message);
          $messages = (new \Swift_Message('Contacter l\'administration de servicestodar'))
        ->setFrom($email)
        ->setTo('servicestodar@gmail.com')
        ->setBody(
            $this->renderView(
                // templates/emails/registration.html.twig
                'mails/nous_contacter.html.twig',
                [
                  'message' => $message
                ]
            ),
            'text/html'
        );
        $mail->send($messages);

           // $flashy->success('Votre message a été envoyé avec succès, nous vous réponderons dans les bref délais');
            return $this->redirectToRoute('app_accueil');
        }
        $categories = $categorieMetierRepository->findAll();
        $user = $this->getUser();
        if ($user) {
            foreach ($user->getRoles() as $role) {
                # code...
                if ($role == 'ROLE_CLIENT') {
                    return $this->render('client/accueil.html.twig', ['images' => $images, 'categories' => $categories, 'form' => $form->createView()]);
                } else if ($role == 'ROLE_FOURNISSEUR') {
                    $date=new DateTime('now');
                    $res=$date->diff($this->getUser()->getDateExpiration());
                    $res=(int) $res->format('%R%a');
                    $commandes=$commandeRepository->commandeTrie($this->getUser());
                    if($res<0){
                        $user->setAbonnement(false);
                        $entityManagerInterface->flush();
                    }
                    else{
                        if($user->getAbonnement()==false){
                            $user->setAbonnement(true);
                            $entityManagerInterface->flush();
                        }
                    }
                    return $this->render('fournisseur/accueil.html.twig', ['images' => $images, 'categories' => $categories, 'form' => $form->createView(),'commandes'=>$commandes,'res'=>$res,'erreur'=>$erreur,]);
                } else {
                    return $this->render('admin/accueil.html.twig', ['images' => $images, 'categories' => $categories, 'form' => $form->createView()]);
                }
            }
        } else {
            return $this->render('invite/accueil.html.twig', ['images' => $images, 'categories' => $categories, 'form' => $form->createView(),'valider'=>$valider]);
        }
    }


    /**
     * @Route("/categories_metier",name="app_categories_metier")
     */
    public function  categoriesMetier(CategorieMetierRepository $categorieMetierRepository)
    {
        $user = $this->getUser();
        $categories = $categorieMetierRepository->findAll();
        if ($user) {
            foreach ($user->getRoles() as $role) {
                # code...
                if ($role == 'ROLE_CLIENT') {
                    return $this->render('client/categories_metier.html.twig', ['categories' => $categories]);
                } else if ($role == 'ROLE_FOURNISSEUR') {
                    return $this->render('fournisseur/categories_metier.html.twig', ['categories' => $categories]);
                } else {
                    return $this->render('admin/categories_metier.html.twig', ['categories' => $categories]);
                }
            }
        } else {
            return $this->render('invite/categories_metier.html.twig', ['categories' => $categories]);
        }
    }


    


    public function isFournisseur(Utilisateur $user)
    {
        foreach ($user->getRoles() as $role) {
            # code...
            if ($role == 'ROLE_FOURNISSEUR') {
                return true;
            }
        }
        return false;
    }



    /**
     * @Route("/type_utilisateur",name="app_type_user")
     */
    public function choixUtilisateur()
    {
        return $this->render('registration/type_utilisateur.html.twig');
    }

    /**
     * IsGranted("ROLE_USER")
     * @Route("/mon_profil",name="app_profil")
     */
    public function monProfil(){
        $user=$this->getUser();
        foreach ($user->getRoles() as $role) {
            # code...
            if($role=='ROLE_CLIENT'){
                return $this->render('client/mon_profil.html.twig',[
                    'user'=>$user
                ]);
            }
            else if($role=='ROLE_FOURNISSEUR'){
                return $this->render('fournisseur/mon_profil.html.twig',[
                    'user'=>$user
                ]);
            }
            else{
                return $this->render('admin/mon_profil.html.twig',[
                    'user'=>$user
                ]);
            }
        }
    }


    public function envoiDeMail($to,$from,$subject,$message){
        $headers = "Envoyé depuis: <$from>\r\n"."Content-type:text/html";
        mail($to, $subject, $message, $headers);
        return null;
    }
    public function estFournisser(Utilisateur $user){
        foreach ($user->getRoles() as $role) {
            # code...
            if($role=='ROLE_FOURNISSEUR'){
                return true;
            }
        }
        return false;
    }
    /**
     * @IsGranted("ROLE_FOURNISSEUR",message="Seul un fournisseur peut payer un abonnement  ")
     * @Route("/paiement/",name="app_paiement")
     */
    public function procederAuPaiement(Request $request,EntityManagerInterface $entityManagerInterface){
        if($this->isFournisseur($this->getUser())){
            $date=new DateTime('now');
            $res=$date->diff($this->getUser()->getDateExpiration());
            $res=(int) $res->format('%R%a');
            if($res<=11){
                $intent['client_secret']='';
            if($request->request->count()==0){
                $prix=(float)50;
            \Stripe\Stripe::setApiKey('sk_test_51IytgEHx0Pz6HlZSI3ofvHKrSpt1Pv0JNOUdBpnv4WTrcjnJr3aI8Lccw0942i2SoitbCCUHQctRYCMqGckFvWA900Kr9Q4RSr');
            $intent=\Stripe\PaymentIntent::create(
                [
                    'amount'=>$prix*100,
                    'currency'=>'eur'
                ]
            );
            }
            return $this->render('paiement/paiement_consulter.html.twig',[
                'code'=>$intent['client_secret'],'res'=>$res
            ]);
            }
            else{
                return $this->redirectToRoute('app_accueil');
            }
            
        }
        else{
            return $this->redirectToRoute('app_accueil');
        }
    }

    /**
     * @Route("/activer/abonnement/{user}",name="app_activer_abonnement")
     */
    public function activerAbonnement(Utilisateur $user,EntityManagerInterface $entityManagerInterface){
        //$user=$this->getUser();
                $user->setAbonnement(true);
               // $user->setNbrJourRestant(30);
                $user->setDateExpiration($this->creationDeDate(30));
                $entityManagerInterface->flush();
                return $this->redirectToRoute('app_accueil');
    }

    public function creationDeDate($a)
    {
        $date = new DateTime('now');
        $t = 'P' . $a . 'D';
        $interval = new DateInterval($t);
        $date->add($interval);
        return $date;
    }
    /**
     * @Route("/chercher_des_fournisseurs",name="app_chercher_fournisseurs")
     */
    public function chercherFournisseurs(Request $request, CategorieMetierRepository $categorieMetierRepository,$res=null)
    {
        $user = $this->getUser();
        $recherche = false;
        $usersFounded = array();
        $utilisateur = new Utilisateur();
        $utilisateur->setNom('Doe');
        $utilisateur->setPrenom('John');
        $utilisateur->setPassword('123456');
        $utilisateur->setTel(rand(100, 200));
        $form = $this->createForm(RechercheFournisseursType::class, $utilisateur);
        $form->handleRequest($request);
        //$form->get('categorieMetier')->setData($categorieMetierRepository->findAll());
        if ($form->isSubmitted() && $form->isValid()) {
            $users = $categorieMetierRepository->findOneBy(['titre' => $utilisateur->getCategorieMetier()->getTitre()])->getUtilisateurs();
            $j=0;
            $t=[];
            while($j<count($users)){
                if($users[$j]->getDateExpiration()==null||$this->estFournisser($users[$j])==false|| $users[$j]->getIsBlocked()==true||$users[$j]->getIsActive()==false||$users[$j]->getAbonnement()==false){}
                else{
                    array_push($t,$users[$j]);
                }
                $j+=1;
            }
            $users=$t;
            if ($users) {
                if ($utilisateur->getGouvernorat() == 'Toute') {
                    $usersFounded = $users;
                    $recherche = true;
                } else {
                    $i = 0;
                    $recherche = true;
                    while ($i < count($users)) {
                        if ($users[$i]->getGouvernorat() == $utilisateur->getGouvernorat() && $this->isFournisseur($users[$i])) {
                            array_push($usersFounded, $users[$i]);
                        }
                        $i++;
                    }
                }
            } else {
                $recherche = true;
            }
        }
        if ($user) {
            foreach ($user->getRoles() as $role) {
                # code...
                if ($role == 'ROLE_CLIENT') {
                    return $this->render('client/chercher_fournisseur.html.twig', ['form' => $form->createView(), 'foundedUsers' => $usersFounded, 'recherche' => $recherche]);
                } else if ($role == 'ROLE_FOURNISSEUR') {
                    $date=new DateTime('now');
                    $res=$date->diff($this->getUser()->getDateExpiration());
                    $res=(int) $res->format('%R%a');
                    return $this->render('fournisseur/chercher_fournisseur.html.twig', ['form' => $form->createView(), 'foundedUsers' => $usersFounded, 'recherche' => $recherche,'res'=>$res]);
                } else {
                    return $this->render('admin/chercher_fournisseur.html.twig', ['form' => $form->createView(), 'foundedUsers' => $usersFounded, 'recherche' => $recherche]);
                }
            }
        } else {
            return $this->render('invite/chercher_fournisseur.html.twig', ['form' => $form->createView(), 'foundedUsers' => $usersFounded, 'recherche' => $recherche]);
        }
    }
}
