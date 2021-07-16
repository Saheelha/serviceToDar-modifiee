<?php

namespace App\Controller;

use DateTime;
use Mpdf\Mpdf;
use DateInterval;
use Dompdf\Dompdf;
use Dompdf\Options;
use App\Entity\Produit;
use App\Entity\Service;
use App\Entity\Commande;
use App\Entity\Utilisateur;
use App\Entity\CommandeProduit;
use App\Entity\CommandeService;
use App\Form\CommandeProduitType;
use App\Form\CommandeServiceType;
use App\Repository\CommandeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CommandeController extends AbstractController
{
    /**
     * @Route("/consulter_commandes/{user}", name="app_consulter_commandes")
     */
    public function consulterCommandes(Utilisateur $user,CommandeRepository $commandeRepository): Response
    {
        foreach ($this->getUser()->getRoles() as $role) {
            # code...
            if($role=="ROLE_CLIENT"){
                $commandes = $commandeRepository->trouverCommandeTrieParDate($user);
                return $this->render('commande/index.html.twig', [
                    'commandes' => $commandes
                ]);
            }
            if($role=="ROLE_ADMIN"){
                $commandes = $user->getCommandes();
        // $commandeProduits=$commandes->getProduit();
        //  dd($commandes);
        $commandeProduits = [];
        $i = 0;
        $commandeServices = [];
        while ($i < count($commandes)) {
            # code...
            if ($commandes[$i]->getCommandeProduit() != null) {
                array_push($commandeProduits, $commandes[$i]->getCommandeProduit());
            } else {
                array_push($commandeServices, $commandes[$i]->getCommandeService());
            }
            $i += 1;
        }
        // dd($commandeProduits,$commandeServices);
        return $this->render('commande/consulter_Commandes.html.twig', [
            'commandeProduits' => $commandeProduits,
            'commandeServices' => $commandeServices,
            'commandes' => $commandes,
            'user'=>$user
        ]);
            }
        }
       
    }




    /**
     * @IsGranted("ROLE_CLIENT",message="Seul un fournisseur peut accéder a ce contenu !  ")
     * @Route("/passer_commande/service/{service}",name="app_commande_service")
     */
    public function passerCommandeService(Service $service, Request $request, EntityManagerInterface $entityManager)
    {
        $commandeService = new CommandeService();
        $commandeService->setService($service);
        $commandeService->setPrix($service->getPrix());
        $form = $this->createForm(CommandeServiceType::class, $commandeService);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            # code...
            $commande = new Commande();
            $commande->setUtilisateur($this->getUser());
            $commandeService->setAdresseFactorisation($this->getUser()->getAdresse());
            $commandeService->setDateReservation($this->creationDeDate($service->getDateReservation()));
            $commande->addServiceCommande($commandeService);
            $commande->setCreatedAt(new DateTime('now'));
            $commande->setRef(md5(uniqid()));
            $commande->setEtat('En attente');
            $entityManager->persist($commande);
            $entityManager->flush();
            return $this->redirectToRoute('app_service_details', [
                'service' => $service->getId()
            ]);
        }
        return $this->render('commande/passer_commande_service.html.twig', [
            'form' => $form->createView()
        ]);
    }


    /**
     * @IsGranted("ROLE_CLIENT",message="Seul un fournisseur peut accéder a ce contenu !  ")
     * @Route("/passer_commande/produit/{produit}",name="app_commande_produit")
     */
    public function passerCommandeProduit(Produit $produit, Request $request, EntityManagerInterface $entityManager)
    {
        $error = '';
        $commandeProduit = new CommandeProduit();
        $form = $this->createForm(CommandeProduitType::class, $commandeProduit);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            //$produit->setQuantite($produit->getQuantite() - $commandeProduit->getQte());
                $commandeProduit->setProduit($produit);
                $commandeProduit->setDateLivraison($this->creationDeDate($produit->getDateLivraison()));
                $commandeProduit->setAdresseFactorisation($this->getUser()->getAdresse());
                $commande = new Commande();
                $commandeProduit->setPrix($produit->getPrix());
                $commande->setUtilisateur($this->getUser());
                $commande->addCommandeProduit($commandeProduit);
                $commande->setCreatedAt(new DateTime('now'));
                $commande->setRef(md5(uniqid()));
                $commande->setEtat('En attente');
                $entityManager->persist($commande);
                $entityManager->flush();
                return $this->redirectToRoute('app_details_produit', [
                    'produit' => $produit->getId()
                ]);
        }
        return $this->render('commande/passer_commande_produit.html.twig', [
            'form' => $form->createView(),
            'error' => $error,
            'qtemax' => $produit->getQuantite()
        ]);
    }

    /**
     * @Route("/generer_facture/{commande}",name="app_generate_facture")
     */
    public function creationFactureProduit(Commande $commande)
    {
        $commandeProduit = $commande->getCommandeProduit()[0];
        $produit = $commandeProduit->getProduit();
        $commandeProduit->setPrixHTTotal($commandeProduit->getPrix() * $commandeProduit->getQte());
        $pdf = new Mpdf();
        $startHead = "<!DOCTYPE html><html><head>";
        $style = "<style>
    *{
        font-size: large;
        font-family: Arial, Helvetica, sans-serif;
    }
   .image{
       width:200px;
       height:200px;
       body {
        font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
        text-align: center;
        color: #777;
    }

    .container{
        width: 100%;
        margin-left: 5px;
        margin-right: 5px;
        margin-top: 25px;
        margin-bottom: 25px;
        display: block;
        position: relative;
    }
    
    .container-s{
        width: 75%;
        margin-left: 5px;
        margin-right: 5px;
        background-color: crimson;
        margin-top: 25px;
        margin-bottom: 25px;
        display: block;
        position: relative;
    }
    .row{
        width:100%;
    }
    .rowb{
        width:90%;
    }
    .p{
        color:grey;

    }
    .table,.tr,.th,.td{
        border: 1px solid black;
        border-collapse: collapse;
    }
    .td,.th{
        padding: 5px;
        text-align:center;
    }
    .th{
        background-color:lightgrey;
    }
    .foot{
        width:100%;
       /* background-color:lightgrey;*/
        font-size: small;
    }
    .foot p{
        text-align:center;
    }
    h5{
        margin-bottom:10px;
    }
    </style>
    ";
        $finishHead = "</head>";
        $startBody = "<body>";
        $finishBody = "</body></html>";
        if($commandeProduit->getProduit()!=null){
            $produit=$commandeProduit->getProduit();
        }
        if($commandeProduit->getAncientProduit()!=null){
            $produit=$commandeProduit->getAncientProduit();
        }
        $date = $commande->getCreatedAt()->format('d/m/y');
        $data = "<img src='" . $this->getParameter('images_directory_logo') . 'logo.png' . "' class='image' />";
        $prixTotalLivraisonSansTaxe = $commandeProduit->getPrixHTTotal() + $produit->getPrixLivraison();
        $ttc = $prixTotalLivraisonSansTaxe * ($commandeProduit->getTaxe() + 1);
        $commandeProduit->setPrixTtc($ttc);
        $content = "
<div>
<table style='width:100%;'>
<tr>
<td>$data</td>
<td>
<h5>Numéro de facture</h5>
" . $commande->getId() . "<br>
<h5>Date de facturation</h5>
" . $date . "
</td>
</tr>
</table>
</div>
<br><br><br><br>
<div>
<table style='width:100%;'>
<tr>
<td>
<h5>Adresse de livraison</h5>
<p class='p'>
" . $commandeProduit->getGouvernorat().' '.$commandeProduit->getLieuLivraison() . "<br>
Téléphone : " . $produit->getUtilisateur()->getTel() . "
</p>
</td>
<td>
<h5>Adresse de facturation</h5>
<p class='p'>
" .$commande->getUtilisateur()->getGouvernorat().' '.$commande->getUtilisateur()->getAdresse() . "<br>
Téléphone : " . $commande->getUtilisateur()->getTel() . "
</p>
</td>
</tr>
</table>
</div>
<br><br><br>
<div>
<table style='width:100%;' class='table'>
<tr class='tr'>
<th class='th'>Numéro de facture</th>
<th class='th'>Date de facturation</th>
<th class='th'>Référence de commande</th>
<th class='th'>Date de livraison</th>
</tr>
<tr class='tr'>
<td class='td'>" . $commande->getId() . "</td>
<td class='td'>$date</td>
<td class='td'>" . $commande->getRef() . "</td>
<td class='td'>" . $commandeProduit->getDateLivraison()->format('d/m/y') . "</td>
</tr>
</table>
</div>
<br><br>
<div>
<table style='width:100%;' class='table'>
<tr class='tr'>
<th class='th'>Référence</th>
<th class='th'>Produit</th>
<th class='th'>Taux de taxe</th>
<th class='th'>Prix unitaire (HT)</th>
<th class='th'>Quantité</th>
<th class='th'>Poids</th>
<th class='th'>Total (HT)</th>
</tr>
<tr class='tr'>
<td class='td'>" . $produit->getRefProd() . "</td>
<td class='td'>" . $produit->getNom() . "</td>
<td class='td'>" . ($commandeProduit->getTaxe() * 100) . " % </td>
<td class='td'>" . $commandeProduit->getPrix() . " TND </td>
<td class='td'>" . $commandeProduit->getQte() . "</td>
<td class='td'>" . $produit->getPoids() . " Kg</td>
<td class='td'>" . $commandeProduit->getPrixHTTotal() . " TND </td>
</tr>
</table>
</div>
<div>
<br><br>
<table style='width:100%;'>
<tr class='tr'>
<td>
<table class='table'>
<tr class='tr'>
<th class='th'>Moyen de paiement</th>
<td class='td'>Paiement comptant à la reception  </td>
</tr>
<tr class='tr'>
<th class='th'>Transporteur</th>
<td class='td'>Livraison à domicile</td>
</tr>
<tr class='tr'>
<th class='th'>Date de livraison</th>
<td class='td'>" . $commandeProduit->getDateLivraison()->format('d/m/y') . "</td>
</tr>
</table>
</td>
<td>
<table class='table'>
<tr class='tr'>
<td class='td'>Totat produit</td>
<td class='td'>" . $commandeProduit->getPrixHTTotal() . " TND </td>
</tr>
<tr class='tr'>
<td class='td'>Frais de livraison</td>
<td class='td'>" . $produit->getPrixLivraison() . " TND </td>
</tr>
<tr class='tr'>
<th class='th'>Totat (HT)</th>";

        $content .= "<td class='td'>" . $prixTotalLivraisonSansTaxe . " TND </td>
</tr>
<tr class='tr'>
<th class='th'>Total</th>
<td class='td'>" . $commandeProduit->getPrixTtc() . " TND </td>
</tr>
</table>
</td>
</tr>
</table>
</div>
<br><br><br><br>
<div class='foot'>
<p>
Tunisie
<br>
Pour toute assistance, merci de nous contacter :
<br>
Tel : 52817963
</p>
</div>
";
        $pdfContent = $startHead . $style . $finishHead . $startBody . $content . $finishBody;
        $pdf->WriteHTML($pdfContent);
        $pdf->SetDisplayMode('fullpage');
        $pdf->SetWatermarkText('facture');
        $pdf->Output('facture.pdf', 'D');
        //$dompdf->render();
        //$dompdf->stream('facture.pdf',array('attachment'=>false));
        return $this->redirectToRoute('commande');
    }


    /**
     * @Route("genere_facture/service/{commande}",name="app_genrer_facture_service")
     */
    public function creationFactureService(Commande $commande)
    {
        $commandeService = $commande->getServiceCommande()[0];
        $service = $commandeService->getService();
        $prixTotalLivraisonSansTaxe = $commandeService->getPrix();
        $ttc = $commandeService->getPrix() * ($commandeService->getTaxe() + 1);
        $commandeService->setPrixTtc($ttc);
        $pdf = new Mpdf();
        $startHead = "<!DOCTYPE html><html><head>";
        $style = "<style>
    *{
        font-size: large;
        font-family: Arial, Helvetica, sans-serif;
    }
   .image{
       width:200px;
       height:200px;
       body {
        font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
        text-align: center;
        color: #777;
    }

    .container{
        width: 100%;
        margin-left: 5px;
        margin-right: 5px;
        margin-top: 25px;
        margin-bottom: 25px;
        display: block;
        position: relative;
    }
    
    .container-s{
        width: 75%;
        margin-left: 5px;
        margin-right: 5px;
        background-color: crimson;
        margin-top: 25px;
        margin-bottom: 25px;
        display: block;
        position: relative;
    }
    .row{
        width:100%;
    }
    .rowb{
        width:90%;
    }
    .p{
        color:grey;

    }
    .table,.tr,.th,.td{
        border: 1px solid black;
        border-collapse: collapse;
    }
    .td,.th{
        padding: 5px;
        text-align:center;
    }
    .th{
        background-color:lightgrey;
    }
    .foot{
        width:100%;
       /* background-color:lightgrey;*/
        font-size: small;
    }
    .foot p{
        text-align:center;
    }
    h5{
        margin-bottom:10px;
    }
    </style>
    ";
    $service=null;
    if($commandeService->getService()!=null){
        $service=$commandeService->getService();
    }
    if($commandeService->getAncientService()!=null){
        $service=$commandeService->getAncientService();
    }
        $finishHead = "</head>";
        $startBody = "<body>";
        $finishBody = "</body></html>";
        $date = $commande->getCreatedAt()->format('d/m/y');
        $data = "<img src='" . $this->getParameter('images_directory_logo') . 'logo.png' . "' class='image' />";
        $content = "
        <div>
        <table style='width:100%;'>
        <tr>
        <td>$data</td>
        <td>
        <h5>Numéro de facture</h5>
        " . $commande->getId() . "<br>
        <h5>Date de facturation</h5>
        " . $date . "
        </td>
        </tr>
        </table>
        </div>
        <br><br><br><br>
        <div>
        <table style='width:100%;'>
        <tr>
        <td>
        <h5>Adresse de livraison</h5>
        <p class='p'>
        " .$commandeService->getGouvernorat().' ' .$commandeService->getLieuReservation() . "<br>
        Téléphone : " . $service->getUtilisateur()->getTel() . "
        </p>
        </td>
        <td>
        <h5>Adresse de facturation</h5>
        <p class='p'>
        " .$commande->getUtilisateur()->getGouvernorat().' '.$commande->getUtilisateur()->getAdresse() . "<br>
        Téléphone : " . $commande->getUtilisateur()->getTel() . "
        </p>
        </td>
        </tr>
        </table>
        </div>
        <br><br><br>
        <div>
        <table style='width:100%;' class='table'>
        <tr class='tr'>
        <th class='th'>Numéro de facture</th>
        <th class='th'>Date de facturation</th>
        <th class='th'>Référence de commande</th>
        <th class='th'>Date de Réservation</th>
        </tr>
        <tr class='tr'>
        <td class='td'>" . $commande->getId() . "</td>
        <td class='td'>$date</td>
        <td class='td'>" . $commande->getRef() . "</td>
        <td class='td'>" . $commandeService->getDateReservation()->format('d/m/y') . "</td>
        </tr>
        </table>
        </div>
        <br><br>
        <div>
        <table style='width:100%;' class='table'>
        <tr class='tr'>
        <th class='th'>Référence</th>
        <th class='th'>Service</th>
        <th class='th'>Taux de taxe</th>
        <th class='th'>Prix unitaire (HT)</th>
        <th class='th'>Total (HT)</th>
        </tr>
        <tr class='tr'>
        <td class='td'>" . $service->getRefService() . "</td>
        <td class='td'>" . $service->getNom() . "</td>
        <td class='td'>" . ($commandeService->getTaxe() * 100) . " % </td>
        <td class='td'>" . $commandeService->getPrix() . " TND </td>
        <td class='td'>" . $ttc . " TND </td>
        </tr>
        </table>
        </div>
        <div>
        <br><br>
        <table style='width:100%;'>
        <tr class='tr'>
        <td>
        <table class='table'>
        <tr class='tr'>
        <th class='th'>Moyen de paiement</th>
        <td class='td'>Paiement comptant à la reception  </td>
        </tr>
        <tr class='tr'>
        <th class='th'>Transporteur</th>
        <td class='td'>Livraison à domicile</td>
        </tr>
        <tr class='tr'>
        <th class='th'>Date de Réservation</th>
        <td class='td'>" . $commandeService->getDateReservation()->format('d/m/y') . "</td>
        </tr>
        </table>
        </td>
        <td>
        <table class='table'>
        <tr class='tr'>
        <td class='td'>Totat produit</td>
        <td class='td'>" . $commandeService->getPrix() . " TND </td>
        </tr>
        <tr class='tr'>
        <td class='td'>Frais de livraison</td>
        <td class='td'>0 TND </td>
        </tr>
        <tr class='tr'>
        <th class='th'>Totat (HT)</th>";

        $content .= "<td class='td'>" . $prixTotalLivraisonSansTaxe . " TND </td>
        </tr>
        <tr class='tr'>
        <th class='th'>Total</th>
        <td class='td'>" . $commandeService->getPrixTtc() . " TND </td>
        </tr>
        </table>
        </td>
        </tr>
        </table>
        </div>
        <br><br><br><br>
        <div class='foot'>
        <p>
        Tunisie
        <br>
        Pour toute assistance, merci de nous contacter :
        <br>
        Tel : 52817963
        </p>
        </div>
        ";
        $pdfContent = $startHead . $style . $finishHead . $startBody . $content . $finishBody;
        $pdf->WriteHTML($pdfContent);
        $pdf->SetDisplayMode('fullpage');
        $pdf->SetWatermarkText('facture');
        $pdf->Output('facture.pdf', 'D');
        return $this->redirectToRoute('commande');
    }


    public function mesProduitCommande()
    {
        $commandes = [];
        foreach ($this->getUser()->getProduits() as $produit) {
            # code...
            foreach ($produit->getCommandeProduit() as $commandeProduit) {
                # code...
                array_push($commandes, $commandeProduit);
            }
        }
        foreach ($this->getUser()->getServices() as $service) {
            # code...
            foreach ($service->getCommandeService() as $commandeService) {
                # code...
                array_push($commandes, $commandeService);
            }
        }
        return $this->render('commande/produit_commande.html.twig', [
            'commande' => $commandes
        ]);
        // $commandeProduit=$produit->getCommandeProduit();
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
     * @Route("/commande/accepter/{commande}",name="app_commande_accepter")
     */
    public function accepterCommande(Commande $commande,EntityManagerInterface $entityManagerInterface,\Swift_Mailer $sender){
        if($commande!=null){
            // $commande->setEtat('Termine');
            
            $user=$commande->getUtilisateur();
            $dateD=null;
            $fichier=null;
            if($commande->getCommandeProduit()!=null){
                foreach ($commande->getCommandeProduit() as $commandeProduit) {
                    # code...
                    $dateD=$commandeProduit->getDateLivraison();
                    $fichier=$this->creationFactureProduitFichier($commande);
                    $cQte=$commandeProduit->getQte();
                    $qteProduit=$commandeProduit->getProduit();
                    //dd($commandeProduit->getProduit());
                    if($cQte> $qteProduit->getQuantite()){
                        return $this->redirectToRoute('app_accueil',['valider'=>0,'erreur'=>1]);
                    }
                    else{
                        $qteProduit->setQuantite($qteProduit->getQuantite()-$cQte);
                        $commandeProduit->setProduit($qteProduit);
                    }
                }
            }
            $commande->setEtat('Termine');
            $entityManagerInterface->flush();
            if($commande->getServiceCommande()!=null){
                foreach ($commande->getServiceCommande() as $commandeService) {
                    # code...
                    $dateD=$commandeService->getDateReservation();
                    $fichier=$this->creationFactureServiceFichier($commande);
                }
            }
            $attachment = new \Swift_Attachment($fichier, 'facture.pdf', 'application/pdf');

            $messages = (new \Swift_Message('Commande accepté '))
            ->setFrom('servicestodar@gmail.com')
            ->setTo($user->getEmail())
            ->setBody(
                $this->renderView(
                    // templates/emails/registration.html.twig
                    'mails/notifier_acceptation_commande.html.twig',
                    [
                        'fournisseur'=>$this->getUser(),
                        'client'=>$user,
                        'dateD'=>$dateD,
                        'commande'=>$commande,
                        'tempsAcceptation'=>new DateTime('now')
                    ]
                ),
                'text/html'
            );
            $messages->attach($attachment);
        $sender->send($messages);
            
        }
        return $this->redirectToRoute('app_accueil');
    }
    ///
    public function creationFactureProduitFichier(Commande $commande)
    {
        $commandeProduit = $commande->getCommandeProduit()[0];
        $produit = $commandeProduit->getProduit();
        $commandeProduit->setPrixHTTotal($commandeProduit->getPrix() * $commandeProduit->getQte());
        $pdf = new Mpdf();
        $startHead = "<!DOCTYPE html><html><head><meta charset='utf-8'>";
        $style = "<style>
    *{
        font-size: large;
        font-family: Arial, Helvetica, sans-serif;
    }
   .image{
       width:200px;
       height:200px;
       body {
        font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
        text-align: center;
        color: #777;
    }

    .container{
        width: 100%;
        margin-left: 5px;
        margin-right: 5px;
        margin-top: 25px;
        margin-bottom: 25px;
        display: block;
        position: relative;
    }
    
    .container-s{
        width: 75%;
        margin-left: 5px;
        margin-right: 5px;
        background-color: crimson;
        margin-top: 25px;
        margin-bottom: 25px;
        display: block;
        position: relative;
    }
    .row{
        width:100%;
    }
    .rowb{
        width:90%;
    }
    .p{
        color:grey;

    }
    .table,.tr,.th,.td{
        border: 1px solid black;
        border-collapse: collapse;
    }
    .td,.th{
        padding: 5px;
        text-align:center;
    }
    .th{
        background-color:lightgrey;
    }
    .foot{
        width:100%;
       /* background-color:lightgrey;*/
        font-size: small;
    }
    .foot p{
        text-align:center;
    }
    h5{
        margin-bottom:10px;
    }
    </style>
    ";
        $finishHead = "</head>";
        if($commandeProduit->getProduit()!=null){
            $produit=$commandeProduit->getProduit();
        }
        if($commandeProduit->getAncientProduit()!=null){
            $produit=$commandeProduit->getAncientProduit();
        }
        $startBody = "<body>";
        $finishBody = "</body></html>";
        $date = $commande->getCreatedAt()->format('d/m/y');
        $data = "<img src='" . $this->getParameter('images_directory_logo') . 'logo.png' . "' class='image' />";
        $prixTotalLivraisonSansTaxe = $commandeProduit->getPrixHTTotal() + $produit->getPrixLivraison();
        $ttc = $prixTotalLivraisonSansTaxe * ($commandeProduit->getTaxe() + 1);
        $commandeProduit->setPrixTtc($ttc);
        $content = "
<div>
<table style='width:100%;'>
<tr>
<td>$data</td>
<td>
<h5>Numéro de facture</h5>
" . $commande->getId() . "<br>
<h5>Date de facture</h5>
" . $date . "
</td>
</tr>
</table>
</div>
<br><br><br><br>
<div>
<table style='width:100%;'>
<tr>
<td>
<h5>Adresse de livraison</h5>
<p class='p'>
" . $commandeProduit->getLieuLivraison() . "<br>
Téléphone : " . $commande->getUtilisateur()->getTel() . "
</p>
</td>
<td>
<h5>Adresse de facturation</h5>
<p class='p'>
" .$this->getUser()->getAdresse() . "<br>
" . $commande->getUtilisateur()->getTel() . "
</p>
</td>
</tr>
</table>
</div>
<br><br><br>
<div>
<table style='width:100%;' class='table'>
<tr class='tr'>
<th class='th'>Numéro de facture</th>
<th class='th'>Date de facturation</th>
<th class='th'>Référence de commande</th>
<th class='th'>Date de livraison</th>
</tr>
<tr class='tr'>
<td class='td'>" . $commande->getId() . "</td>
<td class='td'>$date</td>
<td class='td'>" . $commande->getRef() . "</td>
<td class='td'>" . $commandeProduit->getDateLivraison()->format('d/m/y') . "</td>
</tr>
</table>
</div>
<br><br>
<div>
<table style='width:100%;' class='table'>
<tr class='tr'>
<th class='th'>Référence</th>
<th class='th'>Produit</th>
<th class='th'>Taux de taxe</th>
<th class='th'>Prix unitaire (HT)</th>
<th class='th'>Quantité</th>
<th class='th'>Poids</th>
<th class='th'>Total (HT)</th>
</tr>
<tr class='tr'>
<td class='td'>" . $produit->getRefProd() . "</td>
<td class='td'>" . $produit->getNom() . "</td>
<td class='td'>" . ($commandeProduit->getTaxe() * 100) . " % </td>
<td class='td'>" . $commandeProduit->getPrix() . " TND </td>
<td class='td'>" . $commandeProduit->getQte() . "</td>
<td class='td'>" . $produit->getPoids() . " Kg</td>
<td class='td'>" . $commandeProduit->getPrixHTTotal() . " TND </td>
</tr>
</table>
</div>
<div>
<br><br>
<table style='width:100%;'>
<tr class='tr'>
<td>
<table class='table'>
<tr class='tr'>
<th class='th'>Moyen de paiement</th>
<td class='td'>Paiement comptant à la reception  </td>
</tr>
<tr class='tr'>
<th class='th'>Transporteur</th>
<td class='td'>Livraison à domicile</td>
</tr>
<tr class='tr'>
<th class='th'>Date de livraison</th>
<td class='td'>" . $commandeProduit->getDateLivraison()->format('d/m/y') . "</td>
</tr>
</table>
</td>
<td>
<table class='table'>
<tr class='tr'>
<td class='td'>Totat produit</td>
<td class='td'>" . $commandeProduit->getPrixHTTotal() . " TND </td>
</tr>
<tr class='tr'>
<td class='td'>Frais de livraison</td>
<td class='td'>" . $produit->getPrixLivraison() . " TND </td>
</tr>
<tr class='tr'>
<th class='th'>Totat (HT)</th>";

        $content .= "<td class='td'>" . $prixTotalLivraisonSansTaxe . " TND </td>
</tr>
<tr class='tr'>
<th class='th'>Total</th>
<td class='td'>" . $commandeProduit->getPrixTtc() . " TND </td>
</tr>
</table>
</td>
</tr>
</table>
</div>
<br><br><br><br><br><br><br><br>
<div class='foot'>
<p>
Tunisie
<br>
Pour toute assistance, merci de nous contacter :
<br>
Tel : 52817963
</p>
</div>
";
        $pdfContent = $startHead . $style . $finishHead . $startBody . $content . $finishBody;
        $pdf->WriteHTML($pdfContent);
        $pdf->SetDisplayMode('fullpage');
        $pdf->SetWatermarkText('facture');
        //$dompdf->render();
        //$dompdf->stream('facture.pdf',array('attachment'=>false));
        return $pdf->Output('facture.pdf', 'S');
    }


    
    public function creationFactureServiceFichier(Commande $commande)
    {
        $commandeService = $commande->getServiceCommande()[0];
        $service = $commandeService->getService();
        $prixTotalLivraisonSansTaxe = $commandeService->getPrix();
        $ttc = $commandeService->getPrix() * ($commandeService->getTaxe() + 1);
        $commandeService->setPrixTtc($ttc);
        $pdf = new Mpdf();
        $startHead = "<!DOCTYPE html><html><head><meta charset='utf-8'>";
        $style = "<style>
    *{
        font-size: large;
        font-family: Arial, Helvetica, sans-serif;
    }
   .image{
       width:200px;
       height:200px;
       body {
        font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
        text-align: center;
        color: #777;
    }

    .container{
        width: 100%;
        margin-left: 5px;
        margin-right: 5px;
        margin-top: 25px;
        margin-bottom: 25px;
        display: block;
        position: relative;
    }
    
    .container-s{
        width: 75%;
        margin-left: 5px;
        margin-right: 5px;
        background-color: crimson;
        margin-top: 25px;
        margin-bottom: 25px;
        display: block;
        position: relative;
    }
    .row{
        width:100%;
    }
    .rowb{
        width:90%;
    }
    .p{
        color:grey;

    }
    .table,.tr,.th,.td{
        border: 1px solid black;
        border-collapse: collapse;
    }
    .td,.th{
        padding: 5px;
        text-align:center;
    }
    .th{
        background-color:lightgrey;
    }
    .foot{
        width:100%;
       /* background-color:lightgrey;*/
        font-size: small;
    }
    .foot p{
        text-align:center;
    }
    h5{
        margin-bottom:10px;
    }
    </style>
    ";
    $service=null;
    if($commandeService->getService()!=null){
        $service=$commandeService->getService();
    }
    if($commandeService->getAncientService()!=null){
        $service=$commandeService->getAncientService();
    }
        $finishHead = "</head>";
        $startBody = "<body>";
        $finishBody = "</body></html>";
        $date = $commande->getCreatedAt()->format('d/m/y');
        $data = "<img src='" . $this->getParameter('images_directory_logo') . 'logo.png' . "' class='image' />";
        $content = "
        <div>
        <table style='width:100%;'>
        <tr>
        <td>$data</td>
        <td>
        <h5>Numéro de facture</h5>
        " . $commande->getId() . "<br>
        <h5>Date de facturation</h5>
        " . $date . "
        </td>
        </tr>
        </table>
        </div>
        <br><br><br><br>
        <div>
        <table style='width:100%;'>
        <tr>
        <td>
        <h5>Adresse de livraison</h5>
        <p class='p'>
        " .$commandeService->getGouvernorat().' ' .$commandeService->getLieuReservation() . "<br>
        Téléphone : " . $service->getUtilisateur()->getTel() . "
        </p>
        </td>
        <td>
        <h5>Adresse de facturation</h5>
        <p class='p'>
        " .$commande->getUtilisateur()->getGouvernorat().' '.$commande->getUtilisateur()->getAdresse() . "<br>
        Téléphone : " . $commande->getUtilisateur()->getTel() . "
        </p>
        </td>
        </tr>
        </table>
        </div>
        <br><br><br>
        <div>
        <table style='width:100%;' class='table'>
        <tr class='tr'>
        <th class='th'>Numéro de facture</th>
        <th class='th'>Date de facturation</th>
        <th class='th'>Référence de commande</th>
        <th class='th'>Date de Réservation</th>
        </tr>
        <tr class='tr'>
        <td class='td'>" . $commande->getId() . "</td>
        <td class='td'>$date</td>
        <td class='td'>" . $commande->getRef() . "</td>
        <td class='td'>" . $commandeService->getDateReservation()->format('d/m/y') . "</td>
        </tr>
        </table>
        </div>
        <br><br>
        <div>
        <table style='width:100%;' class='table'>
        <tr class='tr'>
        <th class='th'>Référence</th>
        <th class='th'>Service</th>
        <th class='th'>Taux de taxe</th>
        <th class='th'>Prix unitaire (HT)</th>
        <th class='th'>Total (HT)</th>
        </tr>
        <tr class='tr'>
        <td class='td'>" . $service->getRefService() . "</td>
        <td class='td'>" . $service->getNom() . "</td>
        <td class='td'>" . ($commandeService->getTaxe() * 100) . " % </td>
        <td class='td'>" . $commandeService->getPrix() . " TND </td>
        <td class='td'>" . $ttc . " TND </td>
        </tr>
        </table>
        </div>
        <div>
        <br><br>
        <table style='width:100%;'>
        <tr class='tr'>
        <td>
        <table class='table'>
        <tr class='tr'>
        <th class='th'>Moyen de paiement</th>
        <td class='td'>Paiement comptant à la reception  </td>
        </tr>
        <tr class='tr'>
        <th class='th'>Transporteur</th>
        <td class='td'>Livraison à domicile</td>
        </tr>
        <tr class='tr'>
        <th class='th'>Date de Réservation</th>
        <td class='td'>" . $commandeService->getDateReservation()->format('d/m/y') . "</td>
        </tr>
        </table>
        </td>
        <td>
        <table class='table'>
        <tr class='tr'>
        <td class='td'>Totat produit</td>
        <td class='td'>" . $commandeService->getPrix() . " TND </td>
        </tr>
        <tr class='tr'>
        <td class='td'>Frais de livraison</td>
        <td class='td'>0 TND </td>
        </tr>
        <tr class='tr'>
        <th class='th'>Totat (HT)</th>";

        $content .= "<td class='td'>" . $prixTotalLivraisonSansTaxe . " TND </td>
        </tr>
        <tr class='tr'>
        <th class='th'>Total</th>
        <td class='td'>" . $commandeService->getPrixTtc() . " TND </td>
        </tr>
        </table>
        </td>
        </tr>
        </table>
        </div>
        <br><br><br><br>
        <div class='foot'>
        <p>
        Tunisie
        <br>
        Pour toute assistance, merci de nous contacter :
        <br>
        Tel : 52817963
        </p>
        </div>
        ";
        $pdfContent = $startHead . $style . $finishHead . $startBody . $content . $finishBody;
        $pdf->WriteHTML($pdfContent);
        $pdf->SetDisplayMode('fullpage');
        $pdf->SetWatermarkText('facture');
        //$dompdf->render();
        //$dompdf->stream('facture.pdf',array('attachment'=>false));
        return $pdf->Output('facture.pdf', 'S');
    }

    /**
     * @Route("/commandes_acceptées/{user}",name="app_commande_acceptées")
     */
    public function commendeAcceptees(Utilisateur $user,CommandeRepository $commandeRepository){
       
        $commandes=$commandeRepository->commandeTrieTermine($user);
        return $this->render('commande/commandes_termine.html.twig', [
            'commandes' => $commandes
        ]);
    }

    /**
     * @Route("/refuser/commande/{commande}",name="app_refuser_commande")
     */
    public function refuserCommande(Commande $commande,EntityManagerInterface $entityManagerInterface,\Swift_Mailer $sender){
        if($commande!=null){
            $commande->setEtat('Refusé');
            $entityManagerInterface->flush();
            $messages = (new \Swift_Message('Refus d\'une commande'))
            ->setFrom("servicestodar@gmail.com")
            ->setTo($commande->getUtilisateur()->getEmail())
            ->setBody(
                $this->renderView(
                    // templates/emails/registration.html.twig
                    'mails/notifier_refus_commande.html.twig',
                    [
                      'dateRefus' => new DateTime('now'),
                      'client'=>$commande->getUtilisateur(),
                      'fournisseur'=>$this->getUser()
                    ]
                ),
                'text/html'
            );
            $sender->send($messages);
            return $this->redirectToRoute('app_accueil',['valider'=>0,'erreur'=>0]);

        }
    }
}
