<?php

namespace App\Entity;

use App\Entity\Guests;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\EventParticipantRepository;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity(repositoryClass: EventParticipantRepository::class)]
class EventParticipant
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'eventParticipants')]
    #[ORM\JoinColumn(nullable: false)]
    private ?user $participant = null;

    #[ORM\ManyToOne(inversedBy: 'eventParticipants')]
    #[ORM\JoinColumn(nullable: false)]
    private ?MemberEvents $event = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $comment = null;

    #[ORM\OneToMany(targetEntity: Guests::class, mappedBy: 'eventParticipant', cascade: ['persist', 'remove'])]
    private $guests;

    public function __construct()
    {
        $this->guests = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getParticipant(): ?user
    {
        return $this->participant;
    }

    public function setParticipant(?user $participant): self
    {
        $this->participant = $participant;

        return $this;
    }

    public function getEvent(): ?MemberEvents
    {
        return $this->event;
    }

    public function setEvent(?MemberEvents $event): static
    {
        $this->event = $event;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): static
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * @return Collection|Guest[]
     */
    public function getGuests()
    {
        return $this->guests;
    }

    public function addGuest(Guests $guest): self
    {
        if (!$this->guests->contains($guest)) {
            $this->guests[] = $guest;
            $guest->setEventParticipant($this);
        }

        return $this;
    }

    public function removeGuest(Guests $guest): self
    {
        if ($this->guests->removeElement($guest)) {
            // set the owning side to null (unless already changed)
            if ($guest->getEventParticipant() === $this) {
                $guest->setEventParticipant(null);
            }
        }

        return $this;
    }


}
