<?php

namespace App\Entity;

use App\Entity\Images;
use Cocur\Slugify\Slugify;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\EventsRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EventsRepository::class)]
class Events
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le titre ne peut pas être vide')]
    #[Assert\Length(max: 255, maxMessage: 'Le titre ne peut pas dépasser {{ limit }} caractères')]
    private ?string $titre = null;

    #[ORM\Column(length: 255)]
    private ?string $slug = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank(message: 'La date ne peut pas être vide')]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: 'La description ne peut pas être vide')]
    private ?string $description = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Positive(message: 'Le nombre de places doit être un nombre positif')]
    private ?int $places = null;

    #[ORM\OneToMany(mappedBy: 'events', targetEntity: Images::class, cascade: ['persist', 'remove'])]
    private Collection $images;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'La couverture ne peut pas être vide')]
    private ?string $cover = null;

    #[ORM\OneToMany(mappedBy: 'Event', targetEntity: Reservation::class, orphanRemoval: true)]
    private Collection $reservations;


    public function __construct()
    {
        $this->images = new ArrayCollection();
        $this->reservations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): self
    {
        $this->titre = $titre;

        // Generate the slug from the title
        $slugify = new Slugify();
        $this->slug = $slugify->slugify($titre);

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
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

    public function decrementAvailability(int $numberOfPeople): void
{
    $availablePlaces = $this->getAvailablePlaces();
    $updatedAvailability = $availablePlaces - $numberOfPeople;

    // Ensure the availability doesn't go below zero
    $this->places = max(0, $updatedAvailability);
}

public function getAvailablePlaces(): int
{
    // Calculate and return the available places for the event
    $reservedPlaces = 0;
    
    foreach ($this->reservations as $reservation) {
        $reservedPlaces += $reservation->getNumberOfPeople();
    }

    return max(0, $this->places - $reservedPlaces);
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

    /**
     * @return Collection<int, Reservation>
     */
    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    public function addReservation(Reservation $reservation): static
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations->add($reservation);
            $reservation->setEvent($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): static
    {
        if ($this->reservations->removeElement($reservation)) {
            // set the owning side to null (unless already changed)
            if ($reservation->getEvent() === $this) {
                $reservation->setEvent(null);
            }
        }

        return $this;
    }

   

}
