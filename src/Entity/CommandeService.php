<?php

namespace App\Entity;

use App\Repository\CommandeServiceRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CommandeServiceRepository::class)
 */
class CommandeService
{
    

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateReservation;

    /**
     * @ORM\Column(type="float")
     */
    private $prix;

    /**
     * @ORM\Column(type="float")
     */
    private $taxe;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $lieuReservation;

   
    /**
     * @ORM\ManyToOne(targetEntity=Commande::class, inversedBy="serviceCommande")
     * @ORM\JoinColumn(nullable=false)
     * @ORM\Id
     */
    private $commande;
    private $prixTtc;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $adresseFactorisation;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $gouvernorat;

    /**
     * @ORM\ManyToOne(targetEntity=AncientService::class, inversedBy="commandeService")
     */
    private $ancientService;

    /**
     * @ORM\ManyToOne(targetEntity=Service::class, inversedBy="commandeService")
     */
    private $service;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateReservation(): ?\DateTimeInterface
    {
        return $this->dateReservation;
    }

    public function setDateReservation(\DateTimeInterface $dateReservation): self
    {
        $this->dateReservation = $dateReservation;

        return $this;
    }

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(float $prix): self
    {
        $this->prix = $prix;

        return $this;
    }

    public function getTaxe(): ?float
    {
        return $this->taxe;
    }

    public function setTaxe(float $taxe): self
    {
        $this->taxe = $taxe;

        return $this;
    }

    public function getLieuReservation(): ?string
    {
        return $this->lieuReservation;
    }

    public function setLieuReservation(string $lieuReservation): self
    {
        $this->lieuReservation = $lieuReservation;

        return $this;
    }


    public function getCommande(): ?Commande
    {
        return $this->commande;
    }

    public function setCommande(?Commande $commande): self
    {
        $this->commande = $commande;

        return $this;
    }

    /**
     * Get the value of prixTtc
     */ 
    public function getPrixTtc()
    {
        return $this->prixTtc;
    }

    /**
     * Set the value of prixTtc
     *
     * @return  self
     */ 
    public function setPrixTtc($prixTtc)
    {
        $this->prixTtc = $prixTtc;

        return $this;
    }

    public function getAdresseFactorisation(): ?string
    {
        return $this->adresseFactorisation;
    }

    public function setAdresseFactorisation(?string $adresseFactorisation): self
    {
        $this->adresseFactorisation = $adresseFactorisation;

        return $this;
    }

    public function getGouvernorat(): ?string
    {
        return $this->gouvernorat;
    }

    public function setGouvernorat(?string $gouvernorat): self
    {
        $this->gouvernorat = $gouvernorat;

        return $this;
    }

    public function getAncientService(): ?AncientService
    {
        return $this->ancientService;
    }

    public function setAncientService(?AncientService $ancientService): self
    {
        $this->ancientService = $ancientService;

        return $this;
    }

    public function getService(): ?Service
    {
        return $this->service;
    }

    public function setService(?Service $service): self
    {
        $this->service = $service;

        return $this;
    }
}
