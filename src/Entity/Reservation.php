<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\ReservationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReservationRepository::class)]
#[ApiResource]
class Reservation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'reservations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Events $Event = null;

    #[ORM\Column(length: 255)]
    private ?string $FirstName = null;

    #[ORM\Column(length: 255)]
    private ?string $LastName = null;


    #[ORM\Column(length: 255)]
    private ?string $reservationNumber = null;

    #[ORM\Column]
    private ?int $numberOfpeople = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEvent(): ?Events
    {
        return $this->Event;
    }
   
    public function __toString(): string
    {
        return $this->Event ? $this->Event->__toString() : 'No Event';
    }

    public function setEvent(?Events $Event): static
    {
        $this->Event = $Event;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->FirstName;
    }

    public function setFirstName(string $FirstName): static
    {
        $this->FirstName = $FirstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->LastName;
    }

    public function setLastName(string $LastName): static
    {
        $this->LastName = $LastName;

        return $this;
    }

    public function getReservationNumber(): ?string
    {
        return $this->reservationNumber;
    }

    public function setReservationNumber(string $reservationNumber): static
    {
        $this->reservationNumber = $reservationNumber;

        return $this;
    }

    public function getNumberOfpeople(): ?int
    {
        return $this->numberOfpeople;
    }

    public function setNumberOfpeople(int $numberOfpeople): static
    {
        $this->numberOfpeople = $numberOfpeople;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }
    
}
