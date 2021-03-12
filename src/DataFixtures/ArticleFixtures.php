<?php

namespace App\DataFixtures;

use App\Entity\Article;
use App\Entity\Comment;
use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ArticleFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        // J'importe la librairie Faker installée via composer
        $faker = \Faker\Factory::create('fr_FR');
        // Librairie permettant de créer des données fictives (noms, adresse, phrase,)

        // Création de 3 catégorie
        for($i = 1; $i <= 3; $i++)
        {
            // Pour insérer dans la table Category, nous devons remplir des objets issu de son entité Category::class
            $category = new Category;

            // On appel les setteurs de l'objet
            $category->setTitle($faker->sentence()) // créer des phrases aléatoire
                     ->setDescription($faker->paragraph()); // On crée un paragraphe aléatoire

            $manager->persist($category); // on garde en mémoire et on prépare les requetes d'insertion

            // Création de 4 à 6 articles
            for($j = 1; $j <= mt_rand(4,6); $j++)
            {
                // Pour insérer dans la table Article, nous devons remplir des objets issu de son entité Article::class
                $article = new Article;

                // On crée 5 paragraphes que l'on rassemble en une chaine de caractères (join)
                $content = '<p>' . join($faker->paragraphs(5), '</p><p>') . '</p>';

                $article->setTitle($faker->sentence()) // phrase aléatoire pour le titre de l'article
                        ->setContent($content) // paragraphe aléatoire pour le contenu
                        ->setimage("https://picsum.photos/seed/picsum/200/300") // images aléatoires
                        ->setCreatedAt($faker->dateTimeBetween('-6 months')) // on crée des dates aléatoires d'article de - de 6 mois à ajourd'hui 
                        ->setCategory($category); // on relie les articles aux catégories (clé étrangere)
                
                $manager->persist($article);
                
                // Création de 4 à 10 commentaires pour chaque article

                for($k = 1; $k <= mt_rand(4,10); $k++)
                {
                    // pour inserer dans la table Comment, nous devons remplir des objets issu de son entité Comment::class
                    $comment = new Comment; 

                    // On crée 2 paragraphes que l'on rassemble en une chaine de carateres (join)
                    $content ='<p>' . join($faker->paragraphs(2), '</p><p>') . '</p>';

                    $now = new \DateTime; // retourne la date du jour
                    $interval = $now->diff($article->getCreatedAt()); // retourne un timestamp (temps en secondes) entre la date de création des articles et aujourd'hui
                    $days = $interval->days; // nom de jour entre la date de création des articles et ajourd'hui
                    $minimum = "-$days days"; /* -100 days me bit est d'avoir des commentaires qui à interval de la création des articles, des commentaires de - de 6 mois à ajourd'hui */

                    $comment->setAuthor($faker->name)
                            ->setContent($content)
                            ->setCreatedAt($faker->dateTimeBetween($minimum))
                            ->setArticle($article);

                    $manager->persist($comment);
                }
            }
        }
        $manager->flush();
    }
}
