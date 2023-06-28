<?php

namespace App\Entity;

use App\Repository\CarouselRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CarouselRepository::class)]
class Carousel
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\Type(type: 'string', message: 'Le titre doit être une chaîne de caractères.')]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    #[Assert\Type(type: 'string', message: 'L\'image doit être une chaîne de caractères.')]
    private ?string $image = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

   
    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    
    public function setImage(string $image): self
    {
        $this->image = $image;

        return $this;
    }
}
