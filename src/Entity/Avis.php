<?php

namespace App\Entity;

use App\Repository\AvisRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AvisRepository::class)
 */
class Avis
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     */
    private $contenu;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $note;

    /**
     * @ORM\ManyToOne(targetEntity=Produit::class, inversedBy="avis")
     */
    private $produit;

    /**
     * @ORM\ManyToOne(targetEntity=Service::class, inversedBy="avis")
     */
    private $service;

    /**
     * @ORM\ManyToOne(targetEntity=Utilisateur::class, inversedBy="avis")
     * @ORM\JoinColumn(nullable=false)
     */
    private $utilisateur;

    private $star1;
    private $star2;
    private $star3;
    private $star4;
    private $star5;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContenu(): ?string
    {
        return $this->contenu;
    }

    public function setContenu(string $contenu): self
    {
        $this->contenu = $contenu;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getNote(): ?int
    {
        return $this->note;
    }

    public function setNote(?int $note): self
    {
        $this->note = $note;

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

    public function getService(): ?Service
    {
        return $this->service;
    }

    public function setService(?Service $service): self
    {
        $this->service = $service;

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
     * Get the value of star1
     */ 
    public function getStar1()
    {
        return $this->star1;
    }

    /**
     * Set the value of star1
     *
     * @return  self
     */ 
    public function setStar1($star1)
    {
        $this->star1 = $star1;

        return $this;
    }

    /**
     * Get the value of star2
     */ 
    public function getStar2()
    {
        return $this->star2;
    }

    /**
     * Set the value of star2
     *
     * @return  self
     */ 
    public function setStar2($star2)
    {
        $this->star2 = $star2;

        return $this;
    }

    /**
     * Get the value of star3
     */ 
    public function getStar3()
    {
        return $this->star3;
    }

    /**
     * Set the value of star3
     *
     * @return  self
     */ 
    public function setStar3($star3)
    {
        $this->star3 = $star3;

        return $this;
    }

    /**
     * Get the value of star4
     */ 
    public function getStar4()
    {
        return $this->star4;
    }

    /**
     * Set the value of star4
     *
     * @return  self
     */ 
    public function setStar4($star4)
    {
        $this->star4 = $star4;

        return $this;
    }

    /**
     * Get the value of star5
     */ 
    public function getStar5()
    {
        return $this->star5;
    }

    /**
     * Set the value of star5
     *
     * @return  self
     */ 
    public function setStar5($star5)
    {
        $this->star5 = $star5;

        return $this;
    }
}
