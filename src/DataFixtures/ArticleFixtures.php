<?php

namespace App\DataFixtures;

use App\Entity\Article;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class ArticleFixtures extends Fixture
{
    public function load(ObjetManager $manager)
    {
        // J'importe la librairie Faker installée via composer
        $faker = \Faker\Factory::create('fr_FR');

        // Création de 3 catégorie
        for($i = 1; $i <= 3; $i++)
        {
            $category = new Category;

            $category->setTitle($faker->sentence())
                     ->setDescription($faker->paragraph());

            $manager->persist($category);
        }
    }
}
