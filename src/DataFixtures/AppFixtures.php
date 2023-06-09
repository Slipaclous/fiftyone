<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\News;
use App\Entity\User;
use App\Entity\Images;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $passwordHasher;
    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager)
{
    $faker = Factory::create();

    $admin = new User();
            $admin->setEmail('admin@epse.be')
                ->setRoles(['ROLE_ADMIN'])
                ->setPassword($this->passwordHasher->hashPassword($admin,'password'));

            $manager->persist($admin);
   
    for ($i = 0; $i < 10; $i++) {
        $news = new News();
        $news->setTitre($faker->sentence(6));
        $news->setSoustitre($faker->sentence(6));
        $news->setDescription($faker->paragraph(4));
        $news->setDate($faker->dateTimeBetween('-1 year', 'now'));
        $news->setCover('logo3.gif');

        for ($j = 0; $j < 3; $j++) {
            $image = new Images();
            $image->setUrl('raclette.jpg');
            $news->addImage($image);
            $manager->persist($image);
        }

        $manager->persist($news);
    }

    $manager->flush();
}

}
