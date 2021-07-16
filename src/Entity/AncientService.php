<?php

namespace App\Entity;

use App\Repository\AncientServiceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AncientServiceRepository::class)
 */
class AncientService
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
     * @ORM\Column(type="string", length=255)
     */
    private $dateReservation;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $refService;

    /**
     * @ORM\OneToMany(targetEntity=CommandeService::class, mappedBy="ancientService")
     */
    private $commandeService;

    /**
     * @ORM\ManyToOne(targetEntity=Utilisateur::class, inversedBy="ancientServices")
     */
    private $utilisateur;

    public function __construct()
    {
        $this->commandeService = new ArrayCollection();
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

    public function getDateReservation(): ?string
    {
        return $this->dateReservation;
    }

    public function setDateReservation(string $dateReservation): self
    {
        $this->dateReservation = $dateReservation;

        return $this;
    }

    public function getRefService(): ?string
    {
        return $this->refService;
    }

    public function setRefService(string $refService): self
    {
        $this->refService = $refService;

        return $this;
    }

    /**
     * @return Collection|CommandeService[]
     */
    public function getCommandeService(): Collection
    {
        return $this->commandeService;
    }

    public function addCommandeService(CommandeService $commandeService): self
    {
        if (!$this->commandeService->contains($commandeService)) {
            $this->commandeService[] = $commandeService;
            $commandeService->setAncientService($this);
        }

        return $this;
    }

    public function removeCommandeService(CommandeService $commandeService): self
    {
        if ($this->commandeService->removeElement($commandeService)) {
            // set the owning side to null (unless already changed)
            if ($commandeService->getAncientService() === $this) {
                $commandeService->setAncientService(null);
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
}
