<?php

namespace App\Entity;

use App\Repository\VisitRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VisitRepository::class)]
class Visit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?string $visitorIP = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $visitDate = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVisitorIP(): ?string
    {
        return $this->visitorIP;
    }

    public function setVisitorIP(string $visitorIP): static
    {
        $this->visitorIP = $visitorIP;

        return $this;
    }

    public function getVisitDate(): ?\DateTimeInterface
    {
        return $this->visitDate;
    }

    public function setVisitDate(\DateTimeInterface $visitDate): static
    {
        $this->visitDate = $visitDate;

        return $this;
    }
}
