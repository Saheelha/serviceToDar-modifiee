<?php

namespace App\Entity;

use App\Repository\CommandeProduitRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CommandeProduitRepository::class)
 */
class CommandeProduit
{
    

    /**
     * @ORM\Column(type="integer")
     */
    private $qte;

    /**
     * @ORM\Column(type="float")
     */
    private $prix;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateLivraison;

    /**
     * @ORM\Column(type="float")
     */
    private $taxe;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $lieuLivraison;

   

    /**
     * @ORM\ManyToOne(targetEntity=Commande::class, inversedBy="commandeProduit")
     * @ORM\JoinColumn(nullable=false)
     * @ORM\Id
     */
    private $commande;

    private $prixHTTotal;

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
     * @ORM\ManyToOne(targetEntity=AncientProduit::class, inversedBy="commandeProduit")
     */
    private $ancientProduit;

    /**
     * @ORM\ManyToOne(targetEntity=Produit::class, inversedBy="commandeProduits")
     */
    private $produit;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQte(): ?int
    {
        return $this->qte;
    }

    public function setQte(int $qte): self
    {
        $this->qte = $qte;

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

    public function getDateLivraison(): ?\DateTimeInterface
    {
        return $this->dateLivraison;
    }

    public function setDateLivraison(\DateTimeInterface $dateLivraison): self
    {
        $this->dateLivraison = $dateLivraison;

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

    public function getLieuLivraison(): ?string
    {
        return $this->lieuLivraison;
    }

    public function setLieuLivraison(string $lieuLivraison): self
    {
        $this->lieuLivraison = $lieuLivraison;

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
     * Get the value of prixHTTotal
     */ 
    public function getPrixHTTotal()
    {
        return $this->prixHTTotal;
    }

    /**
     * Set the value of prixHTTotal
     *
     * @return  self
     */ 
    public function setPrixHTTotal($prixHTTotal)
    {
        $this->prixHTTotal = $prixHTTotal;

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
    public function __toString()
    {
        return (string) $this->commande->getId();
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

    public function getAncientProduit(): ?AncientProduit
    {
        return $this->ancientProduit;
    }

    public function setAncientProduit(?AncientProduit $ancientProduit): self
    {
        $this->ancientProduit = $ancientProduit;

        return $this;
    }

    public function getProduit(): ?Produit
    {
        return $this->produit;
    }

    public function setProduit(?Produit $produit): self
    {
        $this->produit = $produit;

        return $this;
    }
}
