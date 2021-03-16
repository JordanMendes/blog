<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleFormType;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminController extends AbstractController
{
    /**
     * Méthode permettant d'afficher l'accueil du BackOffice
     * 
     * @Route("/admin", name="admin")
     */
    public function index(): Response
    {
        return $this->render('admin/index.html.twig');
    }

    /**
     * Méthode permettant d'afficher toute la liste des articles sous formes de tableau HTML dans le BackOffice
     * La deuxieme route permet de supprimer un article dans la BDD en fonction de sont ID
     * 
     * @Route("/admin/articles", name="admin_articles")
     * @Route("/admin/{id}/remove", name="admin_remove_article")
     * 
     */
    public function adminArticles(EntityManagerInterface $manager, ArticleRepository $repoArticle, Article $article = null): Response
    {
        // Le manger permet de manipuler le BDD, on execute la méthode getClassMetadata() afin de selectionne les méta donées des colonnes
        // getFieldNames() permet de seelctionner le noms des champs/colonne de la table Article de la bdd (primary key, not nul, type, taille etc...)
        // $colonne = $data->getColumnMeta() -> $colonne['name']

        $colonnes = $manager->getClassMetadata(Article::class)->getFieldNames();
        
        dump($article);

        // Selection de tout les articles en BDD

        $articles = $repoArticle->findAll();

        dump($articles);

        // Si la condition IF retourne TRUE, cela veut dire que la variable $aricle contient bine l'article a supprimer de la bDD , on entre dans le IF
        if($article)
        {
            // avant la suppression, on stock l'id de l'article a supprimé dans une variable afin de l'injecter dans le message de validation 
            $id = $article->getId();

            $manager->remove($article); // on prépare la requete de suppression (DELETE), on la garde en mémoire
            $manager->flush(); // on execute la requete de suppression 

            $this->addFlash('success', "L'article n°$id a bien été suprimé !");

            return $this->redirectToRoute('admin_articles');
        }

        return $this->render('admin/admin_articles.html.twig', [
            'colonnes' => $colonnes, // On transmet à la méthode render le nom des camps/colonnes selectionnés 
            // en BDD afin de pouvoir les receptionner sur le template et de pouvoir les afficher
            'articlesBdd' => $articles // On transmet à la méthode render les articles selectionnés en BDD au template afin de pouvoir les afficher
        ]);
    }
    /**
     * Méthode permettant de modifier un article existant dans le BackOffice
     * 
     * 
     * @Route("/admin/{id}/edit-article", name="admin_edit_article")
     * 
     * 
     */
    public function adminEditArticle(Article $article, Request $request, EntityManagerInterface $manager)
    {
        dump($article);

        // on crée un formulaire via la class ArticleFormType qui a pour but de remplir l'entité $article
        $formArticle = $this->createForm(ArticleFormType::class, $article);

        dump($request);

        $formArticle->handleRequest($request); // $_POST['title'] --> $article-setTitle($_POST['title'])

        if ($formArticle->isSubmitted() && $formArticle->isValid())
        {
            // On entre dans le IF seulement si le formulaire a bien été validé et que chaque donnée est transmise aux bons setter de l'entité
            $manager->persist($article); // on prépare la requete de modification 
            $manager->flush();// on execute la requete de modification SQL

            // On stock en session un message utilisateur contenant l'id de l'article modifié 
            $this->addFlash('success', "L'article n°" . $article->getId() . " a bien été modifié");

            //Une fois la modification executé, on redirige l'internaute vers l'affichage des articles dans le BackOffice
            return $this->redirectToRoute('admin_articles');
        }

        return $this->render('admin/admin_edit_article.html.twig',[
            'idArticle' =>$article->getId(),
            'formArticle' => $formArticle->createView()
        ]);
    }
    /**
     * Méthode permettant d'afficher sous forme de tableau HTML les catégories stockées en BDD
     * 
     * 
     * @Route("/admin/categories", name="admin_category")
     * @Route("/admin/category/{id}/remove", name="admin_remove_category")
     * 
     */
    public function adminCategory(): Response
    {
        return $this->render('admin/admin_category.html.twig');
    }

    /**
     * @Route("/admin/category/new", name="admin_new_category")
     * @Route("/admin/category{id}/edit", name="admin_edit_category")
     * 
     */
    public function adminFormCategory()
    {
        return $this->render('admin/admin_form_category.html.twig');
    }
}

