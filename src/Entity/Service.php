<?php

namespace App\Entity;

use App\Repository\ServiceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ServiceRepository::class)
 */
class Service
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Assert\NotBlank(message="Le service  doit avoir un nom c'est obligatoire !")
     * @ORM\Column(type="string", length=255)
     */
    private $nom;

    /**
     * @Assert\NotBlank(message="Le service  doit avoir un prix c'est obligatoire !")
     * @ORM\Column(type="float")
     */
    private $prix;

    /**
     * @Assert\NotBlank(message="Le service  doit avoir une description c'est obligatoire !")
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $lienVideo;

    /**
     * @ORM\OneToMany(targetEntity=Image::class, mappedBy="service",cascade={"persist"})
     */
    private $images;

    /**
     * @ORM\ManyToOne(targetEntity=Utilisateur::class, inversedBy="services")
     * @ORM\JoinColumn(nullable=false)
     */
    private $utilisateur;

    /**
     * @ORM\OneToMany(targetEntity=Avis::class, mappedBy="service")
     */
    private $avis;

   

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $dateReservation;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $refService;

    /**
     * @ORM\OneToMany(targetEntity=CommandeService::class, mappedBy="service")
     */
    private $commandeService;

    public function __construct()
    {
        $this->images = new ArrayCollection();
        $this->avis = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getLienVideo(): ?string
    {
        return $this->lienVideo;
    }

    public function setLienVideo(?string $lienVideo): self
    {
        $this->lienVideo = $lienVideo;

        return $this;
    }

    /**
     * @return Collection|Image[]
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(Image $image): self
    {
        if (!$this->images->contains($image)) {
            $this->images[] = $image;
            $image->setService($this);
        }

        return $this;
    }

    public function removeImage(Image $image): self
    {
        if ($this->images->removeElement($image)) {
            // set the owning side to null (unless already changed)
            if ($image->getService() === $this) {
                $image->setService(null);
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

    /**
     * @return Collection|Avis[]
     */
    public function getAvis(): Collection
    {
        return $this->avis;
    }

    public function addAvi(Avis $avi): self
    {
        if (!$this->avis->contains($avi)) {
            $this->avis[] = $avi;
            $avi->setService($this);
        }

        return $this;
    }

    public function removeAvi(Avis $avi): self
    {
        if ($this->avis->removeElement($avi)) {
            // set the owning side to null (unless already changed)
            if ($avi->getService() === $this) {
                $avi->setService(null);
            }
        }

        return $this;
    }

   

    public function __toString()
    {
        return (string) $this->id;
    }

    public function getDateReservation()
    {
        return (string) $this->dateReservation;
    }

    public function setDateReservation(?string $dateReservation): self
    {
        $this->dateReservation = $dateReservation;

        return $this;
    }

    public function getRefService(): ?string
    {
        return $this->refService;
    }

    public function setRefService(?string $refService): self
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
            $commandeService->setService($this);
        }

        return $this;
    }

    public function removeCommandeService(CommandeService $commandeService): self
    {
        if ($this->commandeService->removeElement($commandeService)) {
            // set the owning side to null (unless already changed)
            if ($commandeService->getService() === $this) {
                $commandeService->setService(null);
            }
        }

        return $this;
    }
}
