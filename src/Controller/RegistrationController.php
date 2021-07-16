<?php

namespace App\Controller;

use DateTime;
use DateInterval;
use Swift_Mailer;
use App\Form\SignInType;
use App\Entity\Utilisateur;
use App\Form\SignInClientType;
use App\Form\ResetPasswordType;
use Symfony\Component\Mime\Email;
use App\Form\ForgottenPasswordType;
use App\Form\SignInFournisseurType;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UtilisateurRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use App\Repository\CategorieMetierRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use MercurySeries\FlashyBundle\FlashyNotifier;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\Bridge\Google\Transport\GmailSmtpTransport;
use Symfony\Component\DependencyInjection\Exception\EnvNotFoundException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class RegistrationController extends AbstractController
{
    /**
     * @Route("/registration", name="registration")
     */
    public function index(): Response
    {
        return $this->render('registration/index.html.twig', [
            'controller_name' => 'RegistrationController',
        ]);
    }


    /**
     * inscription coté client
     * @Route("/inscription/client",name="app_sign_in_client")
     */
    public function signinClient(Request $request, EntityManagerInterface $entityManagerInterface, UserPasswordEncoderInterface $encoder, TokenGeneratorInterface $tokenGenerator, MailerInterface $mailer, FlashyNotifier $flashy, \Swift_Mailer $sender): Response
    {
        $user = new Utilisateur();
        $form = $this->createForm(SignInClientType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // mot de passe code avec un algorithme modèrne 
            $password = $user->getPassword();
            $encodedPassword = $encoder->encodePassword($user, $password);
            $user->setPassword($encodedPassword);
            $user->setRoles(['ROLE_CLIENT']);
            $user->setDateCreation(new DateTime());
            $user->setCategorieMetier(null);
            $user->setIsActive(true);
            $user->setIsBlocked(false);

            //creation d'un token d'activation du compte
            $token = $tokenGenerator->generateToken();
            $user->setActivationToken($token);
            // upload photo de profil
            $image = $form->get('photo')->getData();
            if ($image) {
                $fileName = md5(uniqid()) . '.' . $image->guessExtension();
                $image->move(
                    $this->getParameter('images_directory_user_profil'),
                    $fileName
                );
                $user->setPhoto($fileName);
            }


            // creation de mail d'activation 

            //$transport=new GmailSmtpTransport('aminosamineos54@gmail.com','markstive');
            // $mailer=new Mailer($transport);
            $messages = (new \Swift_Message('Activation de compte'))
                ->setFrom('servicestodar@gmail.com')
                ->setTo($user->getEmail())
                ->setBody(
                    $this->renderView(
                        // templates/emails/registration.html.twig
                        'mails/activate_account.html.twig',
                        [
                            'username' => $form->get('username')->getData(),
                            'token' => $token
                        ]
                    ),
                    'text/html'
                );
            $sender->send($messages);
           // $flashy->success('Votre compte a été créer avec succès !');
            $user->setDateExpiration(null);
            $entityManagerInterface->persist($user);
            $entityManagerInterface->flush();
            $valider=1;
            return $this->redirectToRoute('app_accueil',['valider'=>$valider]);
        }

        return $this->render('registration/signin_client.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * inscription coté fournisseur
     * @Route("/inscription/fournisseur",name="app_sign_in_fournisseur")
     */
    public function signinFournisseur(Request $request, EntityManagerInterface $entityManagerInterface, UserPasswordEncoderInterface $encoder, TokenGeneratorInterface $tokenGenerator,  CategorieMetierRepository $categorieMetierRepository, FlashyNotifier $flashy, \Swift_Mailer $sender): Response
    {
        $user = new Utilisateur();
        $form = $this->createForm(SignInFournisseurType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // mot de passe code avec un algorithme modèrne
            $password = $user->getPassword();
            $encodedPassword = $encoder->encodePassword($user, $password);
            $user->setPassword($encodedPassword);
            $user->setRoles(['ROLE_FOURNISSEUR']);
            $user->setDateCreation(new DateTime());
            //creation d'un token d'activation du compte
            $token = $tokenGenerator->generateToken();
            $user->setActivationToken($token);
            $user->setIsActive(true);
            $user->setIsBlocked(false);
            $user->setAbonnement(true);
            // upload de photo de profil
            $image = $form->get('photo')->getData();
            if ($image) {
                $fileName = md5(uniqid()) . '.' . $image->guessExtension();
                $image->move(
                    $this->getParameter('images_directory_user_profil'),
                    $fileName
                );
                $user->setPhoto($fileName);
            }
            $user->setDateExpiration($this->creationDelais(90));
            // envoie des donnée vers la base
            $entityManagerInterface->persist($user);
            $entityManagerInterface->flush();
            // creation de mail d'activation 
            $messages = (new \Swift_Message('Activation de compte'))
                ->setFrom('servicestodar@gmail.com')
                ->setTo($user->getEmail())
                ->setBody(
                    $this->renderView(
                        // templates/emails/registration.html.twig
                        'mails/activate_account.html.twig',
                        [
                            'username' => $form->get('username')->getData(),
                            'token' => $token
                        ]
                    ),
                    'text/html'
                );
            $sender->send($messages);
          //  $flashy->success('Votre compte a été créer avec succès !');
          $valider=1;
          return $this->redirectToRoute('app_accueil',['valider'=>$valider]);
        }

        return $this->render('registration/signin_fournisseur.html.twig', [
            'form' => $form->createView()
        ]);
    }



    /**
     * activation de compte pour les 2 rôles
     * @Route("/activation/{token}",name="app_account_activation")
     */
    public function confirmerInscription($token, UtilisateurRepository $utilisateurRepository, EntityManagerInterface $entityManagerInterface, FlashyNotifier $flashy, \Swift_Mailer $sender)
    {
        //return $this->redirectToRoute('app_login');
        if ($token) {
            $user = $utilisateurRepository->findOneBy(['activationToken' => $token]);
            if ($user) {
                $user->setActivationToken(null);
                $entityManagerInterface->flush();
                $messages = (new \Swift_Message('Compte activé !'))
                    ->setFrom('servicestodar@gmail.com')
                    ->setTo($user->getEmail())
                    ->setBody(
                        $this->renderView(
                            // templates/emails/registration.html.twig
                            'mails/notifier_activation_compte.html.twig',
                            [
                                'username'=>$user->getUsername(),
                                'dateActivation'=>new DateTime('now')
                            ]
                        ),
                        'text/html'
                    );
                $sender->send($messages);
              //  $flashy->info('Votre compte a été activé maintenant ! ');
                return $this->redirectToRoute('app_accueil');
                /// notifier_activation_compte.html.twig
                //return $this->redirectToRoute('app_login');
            } else {
                // return NotFoundException('votre token n\'est pas valide  ! ');
                return $this->redirectToRoute('app_accueil');
            }
        }
        return $this->redirectToRoute('app_accueil');
    }

    /**
     * @Route("/mot_de_passe_oublie",name="app_forgotten_password")
     */
    public function motDePasseOublie(UtilisateurRepository $utilisateurRepository,  TokenGeneratorInterface $tokenGenerator, Request $request, EntityManagerInterface $entityManagerInterface, FlashyNotifier $flashy, \Swift_Mailer $sender)
    {
        $error = '';
        $form = $this->createForm(ForgottenPasswordType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $adresseMail = $form->get('email')->getData();
            $user = $utilisateurRepository->findOneBy(['email' => $adresseMail]);
            if ($user) {
                $token = $tokenGenerator->generateToken();
                $dateExpiration = new \DateTime('+1 day');
                ///
                $messages = (new \Swift_Message('Demande de changement de mot de passe '))
                    ->setFrom('servicestodar@gmail.com')
                    ->setTo($user->getEmail())
                    ->setBody(
                        $this->renderView(
                            // templates/emails/registration.html.twig
                            'mails/reset_password.html.twig',
                            [
                                'username' => $user->getUsername(),
                                'token' => $token,
                                'dateExpiration' => $dateExpiration
                            ]
                        ),
                        'text/html'
                    );
                $sender->send($messages);
                ///
                $user->setPasswordResetedAt($dateExpiration);
                $user->setResetPasswordToken($token);
                $entityManagerInterface->flush();
               // $flashy->info('Un lien de réinitialisation de mot de passe vous a été envoyé');
                //$flashy->success('Un mail qui vous permet de changer de mot de passe vous a été envoyé');
                // $flashy->warning('Le lien qui vous été envoyé n\'est valable que pour 24h ! ');
                return $this->redirectToRoute('app_login');
                //return null;
            } else {
                $error = 'Aucun compte n\'est enregistré avec l\'email  que vous avez soumis ! ';
            }
        }
        return $this->render('registration/forgotten_password.html.twig', [
            'form' => $form->createView(),
            'error' => $error
        ]);
    }

    /**
     * @Route("/confirmer_vouloir_changer_mot_de_passe/{token}",name="app_confirm_resetPassword")
     */
    public function confirmerChangerMotDePasse($token, UtilisateurRepository $utilisateurRepository, FlashyNotifier $flashy)
    {
        if ($token) {
            $user = $utilisateurRepository->findOneBy(['resetPasswordToken' => $token]);
            if ($user) {
                if ($user->getPasswordResetedAt() > new DateTime('now')) {
                    return $this->redirectToRoute('app_reset_password', ['token' => $token]);
                } else {
                    return $this->redirectToRoute('app_login');
                }
            }
        } 
        return $this->redirectToRoute('app_login');
    }

    /**
     * @Route("/change_de_mot_de_passe/{token}",name="app_reset_password")
     */
    public function changerDeMotDePasse($token, UserPasswordEncoderInterface $encoder, UtilisateurRepository $utilisateurRepository, Request $request, FlashyNotifier $flashy,\Swift_Mailer $sender)
    {
        if ($token && $utilisateurRepository->findOneBy(['resetPasswordToken' => $token]) != null) {
            $error = '';
            $form = $this->createForm(ResetPasswordType::class);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $user = $utilisateurRepository->findOneBy(['resetPasswordToken' => $token]);
                $password = $form->get('password')->getData();
                $confirmPassword = $form->get('confirmPassword')->getData();
                if (strlen($password) >= 5) {
                    if ($password == $confirmPassword) {
                        // coder le mot de passe
                        $encodedPassword = $encoder->encodePassword($user, $password);
                        // reinitialiser la date et token du mot de passe
                        $user->setResetPasswordToken(null);
                        $user->setPasswordResetedAt(null);
                        $utilisateurRepository->upgradePassword($user, $encodedPassword);
                        //notifier_mot_de_passe_est_change.html.twig
                        $messages = (new \Swift_Message('Mot de passe changé ! '))
                    ->setFrom('servicestodar@gmail.com')
                    ->setTo($user->getEmail())
                    ->setBody(
                        $this->renderView(
                            // templates/emails/registration.html.twig
                            'mails/notifier_mot_de_passe_est_change.html.twig',
                            [
                              'username'=>$user->getUsername(),
                              'dateChangement'=>new DateTime('now')  
                            ]
                        ),
                        'text/html'
                    );
                $sender->send($messages);
                       // $flashy->success('Votre mot de passe a été reinitiallisé ! ');
                        return $this->redirectToRoute('app_login');
                    } else {
                        $error = 'Les deux champs doivent être identique ! ';
                    }
                } else {
                    $error = 'Le mot de passe doit comporter au moins 5 caractères ! ';
                }
            }
            return $this->render('registration/reset_password.html.twig', [
                'form' => $form->createView(),
                'error' => $error
            ]);
        } else {
            return $this->redirectToRoute('app_login');
        }
    }


    /**
     * @Route("/desactiver_compte/{user}",name="app_user_desactivate_account")
     */
    public function desactiverCompte(Utilisateur $user,EntityManagerInterface $entityManagerInterface,FlashyNotifier $flashy,\Swift_Mailer $sender){
        if($user){
            $user->setIsActive(false);
            $entityManagerInterface->flush();
            $messages = (new \Swift_Message('Désactivation de votre compte !'))
            ->setFrom('servicestodar@gmail.com')
            ->setTo($user->getEmail())
            ->setBody(
                $this->renderView(
                    // templates/emails/registration.html.twig
                    'mails/desactivation_votre_compte.html.twig',
                    [
                        'username'=>$user->getUsername(),
                        'dateDesactivation'=>new DateTime('now')
                    ]
                ),
                'text/html'
            );
        $sender->send($messages);
        return $this->redirectToRoute('app_accueil');
        }
        else{
            return $this->redirectToRoute('app_accueil');
        }
    }
     /**
      * @Route("/activer_compte/{user}",name="app_user_activate_account")
      */
    public function activerCompte(Utilisateur $user,EntityManagerInterface $entityManagerInterface,FlashyNotifier $flashy,\Swift_Mailer $sender){
        if($user){
            $user->setIsActive(true);
            $entityManagerInterface->flush();
            $messages = (new \Swift_Message('Activation de votre compte !'))
            ->setFrom('servicestodar@gmail.com')
            ->setTo($user->getEmail())
            ->setBody(
                $this->renderView(
                    // templates/emails/registration.html.twig
                    'mails/activer_votre_compte.html.twig',
                    [
                        'username'=>$user->getUsername(),
                        'dateActivation'=>new DateTime('now')
                    ]
                ),
                'text/html'
            );
        $sender->send($messages);
        return $this->redirectToRoute('app_accueil');
        }
        else{
            return $this->redirectToRoute('app_accueil');
        }
        
    }
    public function creationDelais($a)
    {
        $date = new DateTime('now');
        $t = 'P' . $a . 'D';
        $interval = new DateInterval($t);
        $date->add($interval);
        return $date;
    }
}
