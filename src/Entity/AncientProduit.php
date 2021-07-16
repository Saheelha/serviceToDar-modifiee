<?php

namespace App\Entity;

use App\Repository\AncientProduitRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AncientProduitRepository::class)
 */
class AncientProduit
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nom;

    /**
     * @ORM\Column(type="float")
     */
    private $prix;

    /**
     * @ORM\Column(type="integer")
     */
    private $quantite;

    /**
     * @ORM\ManyToOne(targetEntity=Utilisateur::class, inversedBy="ancientProduits")
     */
    private $utilisateur;

    /**
     * @ORM\OneToMany(targetEntity=CommandeProduit::class, mappedBy="ancientProduit")
     */
    private $commandeProduit;
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $dateLivraison;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $prixLivraison;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $refProd;

    public function __construct()
    {
        $this->commandeProduit = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

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

    public function getQuantite(): ?int
    {
        return $this->quantite;
    }

    public function setQuantite(int $quantite): self
    {
        $this->quantite = $quantite;

        return $this;
    }

    public function getUtilisateur(): ?Utilisateur
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?Utilisateur $utilisateur): self
    {
        $this->utilisateur = $utilisateur;

        return $this;
    }

    /**
     * @return Collection|CommandeProduit[]
     */
    public function getCommandeProduit(): Collection
    {
        return $this->commandeProduit;
    }

    public function addCommandeProduit(CommandeProduit $commandeProduit): self
    {
        if (!$this->commandeProduit->contains($commandeProduit)) {
            $this->commandeProduit[] = $commandeProduit;
            $commandeProduit->setAncientProduit($this);
        }

        return $this;
    }

    public function removeCommandeProduit(CommandeProduit $commandeProduit): self
    {
        if ($this->commandeProduit->removeElement($commandeProduit)) {
            // set the owning side to null (unless already changed)
            if ($commandeProduit->getAncientProduit() === $this) {
                $commandeProduit->setAncientProduit(null);
            }
        }

        return $this;
    }
    public function getDateLivraison()
    {
        return (string )$this->dateLivraison;
    }

    public function setDateLivraison(?string $dateLivraison): self
    {
        $this->dateLivraison = $dateLivraison;

        return $this;
    }

    public function getPrixLivraison(): ?float
    {
        return $this->prixLivraison;
    }

    public function setPrixLivraison(?float $prixLivraison): self
    {
        $this->prixLivraison = $prixLivraison;

        return $this;
    }

    public function getRefProd(): ?string
    {
        return $this->refProd;
    }

    public function setRefProd(?string $refProd): self
    {
        $this->refProd = $refProd;

        return $this;
    }

    /**
     * Set the value of commandeProduit
     *
     * @return  self
     */ 
    public function setCommandeProduit($commandeProduit)
    {
        $this->commandeProduit = $commandeProduit;

        return $this;
    }
}
