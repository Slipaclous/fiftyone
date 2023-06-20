<?php

namespace App\Entity;

use App\Entity\Images;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\EventsRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity(repositoryClass: EventsRepository::class)]
class Events
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $titre = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(nullable: true)]
    private ?int $places = null;

    #[ORM\OneToMany(mappedBy: 'events', targetEntity: Images::class, cascade: ['persist', 'remove'])]
    private Collection $images;

    #[ORM\Column(length: 255)]
    private ?string $cover = null;


    public function __construct()
    {
        $this->images = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getPlaces(): ?int
    {
        return $this->places;
    }

    public function setPlaces(?int $places): static
    {
        $this->places = $places;

        return $this;
    }

    /**
     * @return Collection<int, images>
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(Images $image): static
    {
        if (!$this->images->contains($image)) {
            $this->images->add($image);
            $image->setEvents($this);
        }

        return $this;
    }

    public function removeImage(Images $image): static
    {
        if ($this->images->removeElement($image)) {
            // set the owning side to null (unless already changed)
            if ($image->getEvents() === $this) {
                $image->setEvents(null);
            }
        }

        return $this;
    }

    public function getCover(): ?string
    {
        return $this->cover;
    }

    public function setCover(string $cover): static
    {
        $this->cover = $cover;

        return $this;
    }

   

}
