<?php

namespace App\Entity;

use App\Entity\EventParticipant;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\GuestsRepository;

#[ORM\Entity(repositoryClass: GuestsRepository::class)]
class Guests
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $prenom = null;
/**
     * @ORM\Column(type="boolean")
     */
    private bool $shouldRemove = false; // Default to false, meaning the guest should not be removed by default

    #[ORM\ManyToOne(inversedBy: 'guests')]
    #[ORM\JoinColumn(nullable: false)]
    private ?EventParticipant $eventParticipant = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getEventParticipant(): ?EventParticipant
    {
        return $this->eventParticipant;
    }

    public function setEventParticipant(?EventParticipant $eventParticipant): self
    {
        $this->eventParticipant = $eventParticipant;

        return $this;
    }
    public function getRemove(): bool
    {
        return $this->shouldRemove;
    }

    /**
     * Set whether this guest should be removed.
     */
    public function setRemove(bool $shouldRemove): self
    {
        $this->shouldRemove = $shouldRemove;

        return $this;
    }
}
