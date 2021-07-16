<?php

namespace App\Controller;

use Mpdf\Mpdf;
use Dompdf\Dompdf;
use Dompdf\Options;
use App\Entity\Produit;
use App\Form\StatistiqueType;
use App\Repository\ProduitRepository;
use App\Repository\ServiceRepository;
use App\Repository\UtilisateurRepository;
use DateTime;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class StatistiqueController extends AbstractController
{

    public function index(Request $request, Produit $produit, ProduitRepository $produitRepository): Response
    {
        $visible = false;
        $v = null;
        $nbr = null;
        $year = null;
        $form = $this->createForm(StatistiqueType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $d = $form->get('annee')->getData();
            $d = (string)($d - 2000);
            $valeurs = [
                '01' => 0,
                '02' => 0,
                '03' => 0,
                '04' => 0,
                '05' => 0,
                '06' => 0,
                '07' => 0,
                '08' => 0,
                '09' => 0,
                '10' => 0,
                '11' => 0,
                '12' => 0
            ];
            foreach ($produit->getCommandeProduit() as $c) {
                # code...
                if ($c->getCommande()->getCreatedAt()->format('y') == $d) {
                    # code...
                    $valeurs[$c->getCommande()->getCreatedAt()->format('m')] += 1;
                }
                $year = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novombre', 'Décembre'];
                $nbr = [$valeurs['01'], $valeurs['02'], $valeurs['03'], $valeurs['04'], $valeurs['05'], $valeurs['06'], $valeurs['07'], $valeurs['08'], $valeurs['09'], $valeurs['10'], $valeurs['11'], $valeurs['12']];
                $visible = true;
            }
            // dd($nbr);
        }
        //dd($n,$v);
        return $this->render('statistique/index.html.twig', [
            'form' => $form->createView(),
            'visible' => $visible,
            'valeurs' => json_encode($nbr),
            'mois' => json_encode($year)
        ]);
    }

    /**
     * @Route("/statistique", name="app_statistique")
     */
    public function consulterStatistique(UtilisateurRepository $utilisateurRepository, ProduitRepository $produitRepository, ServiceRepository $serviceRepository)
    {
        $produitPlusCommande = $produitRepository->troisproduitLesPlusCommandee();
        $produitPlusCommandeNoms = [];
        $produitPlusCommandeValeurs = [];
        $servicePlusCommande = $serviceRepository->troisServicesLesPlusCommandee();
        $servicePlusCommandeNoms = [];
        $servicePlusCommandeValeurs = [];
        $nomProduits=$produitRepository->trouverNomDeChaqueProduit();
        $nomServices=$serviceRepository->trouverNomDeChaqueService();
               $i=0;
               $nomServices=str_replace('\'',"&#039;",$nomServices);
               $nomProduits=str_replace('\'',"&#039;",$nomProduits);
        // while($i<count($nomProduits)){
        //     $produit=$nomProduits[$i];
        //     while(str_contains($produit, '\'')){
        //         $produit=str_replace('\'',"\'",$produit);
        //     }
        //     $nomProduits[$i]=$produit;
        //     $i++;
        // }
        // $i=0;
        // while($i<count($nomServices)){
        //     $service=$nomServices[$i];
        //     while(str_contains($service, '\'')){
        //         $service=str_replace('\'',"\'",$service);
        //     }
        //     $nomServices[$i]=$service;
        //     $i++;
        // }
          if($produitPlusCommande != null){
            foreach ($produitPlusCommande as $produit) {
                # code...
                $nom=$produit->getNom();
                // while(str_contains($nom, '\'')){
                //     $nom=str_replace('\'',"\'",$nom);
                // }
                array_push($produitPlusCommandeNoms, $nom);
                array_push($produitPlusCommandeValeurs, count($produit->getCommandeProduits()));
            }
          }
        
       if($servicePlusCommande != null){
        foreach ($servicePlusCommande as $service) {
            # code...
            $nom=$service->getNom();
            // while(str_contains($nom, '\'')){
            //     $nom=str_replace('\'',"\'",$nom);
            // }
            array_push($servicePlusCommandeNoms, $nom);
            array_push($servicePlusCommandeValeurs, count($service->getCommandeService()));
        }
       }
        //dd($nomServices);
        //dd($servicePlusCommande);
        $servicePlusCommandeNoms=str_replace('\'',"&#039;",$servicePlusCommandeNoms);
        $produitPlusCommandeNoms=str_replace('\'',"&#039;",$produitPlusCommandeNoms);
        return $this->render('stat/consulter_stat.html.twig', [
            'nbrClient' => $utilisateurRepository->nbrClients(),
            'nbrFournisseur' => $utilisateurRepository->nbrFourniseurs(),
            'produits' => json_encode($produitRepository->trouverChaqueProduitNombreDeCommandeRecu()),
            'services' => json_encode($serviceRepository->trouverChaqueServiceNombreDeCommandeRecu()),
            'nomProduits' => json_encode($nomProduits),
            'nomServices' => json_encode($nomServices),
            'nbrProduits' => $produitRepository->nbrProduit(),
            'nbrServices' => $serviceRepository->nbrService(),
            'produitPlusCommandeNoms' => json_encode($produitPlusCommandeNoms),
            'produitPlusCommandeValeurs' => json_encode($produitPlusCommandeValeurs),
            'servicePlusCommandeNoms' => json_encode($servicePlusCommandeNoms),
            'servicePlusCommandeValeurs' => json_encode($servicePlusCommandeValeurs)
        ]);
    }


    /**
     * @Route("/generer_stat/{numero}",name="app_generate_stat")
     */
    public function creationFactureProduit($numero, UtilisateurRepository $utilisateurRepository, ProduitRepository $produitRepository, ServiceRepository $serviceRepository)
    {
        $pdf = new Mpdf();
        $startHead = "<!DOCTYPE html><html><head>";
        $style = "<style>
    *{
        font-size: large;
        font-family: Arial, Helvetica, sans-serif;
    }
    table,tr,td{
        width:100%;
    }
    td h1{
        text-align:center;
        font-size: x-large;
    }
    .chart{
        width: inherit;
        height: inherit;
      }
   
    </style>
    ";
    $logo="<img src='" . $this->getParameter('images_directory_logo') . 'logo.png' . "' class='image' />";
        $finishHead = "</head>";
        $startBody = "<body>";
        $finishBody = "</body></html>";
        $firstRow="
        <table>
        <tr>
        <td>".$logo."</td>
        <td><h1>Nombre des utilisateur par type :</h1></td>
        </tr>
        </table>
        ";
        switch ($numero) {
            case 1:
                # code...
                $nbrClients=$utilisateurRepository->nbrClients();
                $nbrFournisseurs=$utilisateurRepository->nbrFourniseurs();
                $data="".$firstRow."
                <canvas id='stat'  class='chart'></canvas>
                <script
      src='https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.2.0/chart.min.js'
      integrity='sha512-VMsZqo0ar06BMtg0tPsdgRADvl0kDHpTbugCBBrL55KmucH6hP9zWdLIWY//OTfMnzz6xWQRxQqsUFefwHuHyg=='
      crossorigin='anonymous'
    ></script>
                <script>
                var nbrClients=JSON.parse(".json_encode($nbrClients).");
                var nbrFournisseur=JSON.parse(".json_encode($nbrFournisseurs).");
                var ctx=document.getElementById('stat').getContext('2d');
                var diagramme=new Chart(ctx, {
                  type: 'pie',
                  data: {
                      labels: ['Clients','Fournisseurs'],
                      datasets: [{
                          label: 'Nombre des utilisateurs pour chaque type',
                          data: [nbrClients,nbrFournisseur],
                          backgroundColor: [
                              'rgba(54, 162, 235, 0.2)',
                              'rgba(255, 206, 86, 0.2)',
                              'rgba(75, 192, 192, 0.2)',
                              'rgba(153, 102, 255, 0.2)',
                              'rgba(255, 159, 64, 0.2)'
                          ],
                          borderColor: [
                              'rgba(54, 162, 235, 1)',
                              'rgba(255, 206, 86, 1)',
                              'rgba(75, 192, 192, 1)',
                              'rgba(153, 102, 255, 1)',
                              'rgba(255, 159, 64, 1)'
                          ],
                          borderWidth: 1
                      }]
                  }
              });
                </script>
                ";
                break;
            case 2:
                # code...
                break;
            case 3:
                # code...
                break;
            case 4:
                # code...
                break;
            case 5:
                # code...
                break;
            case 6:
                # code...
                break;
            // case 1:
            //     # code...
            //     break;
            // case 1:
            //     # code...
            //     break;
            // case 1:
            //     # code...
            //     break;

            default:
                # code...
                break;
        }
        $pdfContent = $startHead . $style . $finishHead . $startBody . $data . $finishBody;
        $pdf->WriteHTML($pdfContent);
        $pdf->SetDisplayMode('fullpage');
        $pdf->SetWatermarkText('facture');
        $date=new DateTime('now');
        $name=$date->format('d-m-y h:m:s');
        $pdf->Output('stat '.$name.'.pdf', 'D');
        return $this->redirectToRoute('app_statistique');
    }
}
