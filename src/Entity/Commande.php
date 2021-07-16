<?php

namespace App\Entity;

use App\Repository\CommandeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CommandeRepository::class)
 */
class Commande
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\OneToMany(targetEntity=CommandeProduit::class, mappedBy="commande", orphanRemoval=true,cascade={"persist"})
     */
    private $commandeProduit;

    /**
     * @ORM\OneToMany(targetEntity=CommandeService::class, mappedBy="commande", orphanRemoval=true,cascade={"persist"})
     */
    private $serviceCommande;

    /**
     * @ORM\ManyToOne(targetEntity=Utilisateur::class, inversedBy="commandes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $utilisateur;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $ref;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $etat;

    

    public function __construct()
    {
        $this->commandeProduit = new ArrayCollection();
        $this->serviceCommande = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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
            $commandeProduit->setCommande($this);
        }

        return $this;
    }

    public function removeCommandeProduit(CommandeProduit $commandeProduit): self
    {
        if ($this->commandeProduit->removeElement($commandeProduit)) {
            // set the owning side to null (unless already changed)
            if ($commandeProduit->getCommande() === $this) {
                $commandeProduit->setCommande(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|CommandeService[]
     */
    public function getServiceCommande(): Collection
    {
        return $this->serviceCommande;
    }

    public function addServiceCommande(CommandeService $serviceCommande): self
    {
        if (!$this->serviceCommande->contains($serviceCommande)) {
            $this->serviceCommande[] = $serviceCommande;
            $serviceCommande->setCommande($this);
        }

        return $this;
    }

    public function removeServiceCommande(CommandeService $serviceCommande): self
    {
        if ($this->serviceCommande->removeElement($serviceCommande)) {
            // set the owning side to null (unless already changed)
            if ($serviceCommande->getCommande() === $this) {
                $serviceCommande->setCommande(null);
            }
        }

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

    public function getRef(): ?string
    {
        return $this->ref;
    }

    public function setRef(?string $ref): self
    {
        $this->ref = $ref;

        return $this;
    }

    public function getEtat(): ?string
    {
        return $this->etat;
    }

    public function setEtat(?string $etat): self
    {
        $this->etat = $etat;

        return $this;
    }
  public  function comparator($object1, $object2) {
        return $object1->id > $object2->id;
    }

 
}
