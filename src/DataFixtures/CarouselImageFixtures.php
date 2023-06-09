<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Carousel;

class CarouselImageFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $images = [
            ['image' => 'carou1.jpg', 'title' => 'Image 1 Title'],
            ['image' => 'raclette.jpg', 'title' => 'Image 2 Title'],
        ];

        foreach ($images as $imageData) {
            $carouselImage = new Carousel();
            $carouselImage->setImage($imageData['image']);
            $carouselImage->setTitle($imageData['title']);

            $manager->persist($carouselImage);
        }

        $manager->flush();
    }
}
