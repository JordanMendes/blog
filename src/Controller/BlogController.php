<?php

namespace App\Controller;

use App\Entity\Article;
use App\Repository\ArticleRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BlogController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function home(): Response
    {
        return $this->render('blog/home.html.twig', [
            'title' => "Bienvenue sur le blog Symfony", 
            'age'=>25
        ]);
    }
    /**
     * Méthode permettant d'afficher toute les listes des articles stockés en BDD
     * 
     * @Route("/blog", name="blog")
     */
    public function index(ArticleRepository $repo): Response
    {
        /*
            Pour selectionner des données dans une table SQL, nous devons absolument avoir accès à la classe Repository de l'entité correspondante 
            Un Repository est une classe permettant uniquement d'executer des requetes de selection en BDD (SELECT)
            Nous devons donc accéder au repository de l'netité Article au sein de notre controller  

            On appel l'ORM doctrine (getDoctrine()), puis on importe le repositoritory de la classe Article grace à la méthode getRepository()
            $repo est un objet issu de la classe ArticleRepository
            cet objet contient des méthodes permettant d'executer des requetes de selections
            findAll() : méthode issue de la classe ArticleRepository permettant de selectionner l'ensemble de la table SQL 'Article'
        */

        //$repo = $this->getDoctrine()->getRepository(Article::class); // equivalent e SELECT * FROM article + fetchAll

        //outils de débugage de Symfony (equivalent d'un var_dump)
        dump($repo);

        $articles = $repo->findAll();

        //Outil de débugage de Symfony ( equivalent d'un var_dump en php)
        dump($articles);

        return $this->render('blog/index.html.twig', [
            'title' => 'Liste des Articles',
            'articles' => $articles // on envoie sur le template, les articles selectionnés en BDD afin de pouvoir les afficher dynamiquement sur le template à l'aide du langage Twig

        ]);
    }

    /**
     * @Route("/blog/new", name="blog_create")
     */
    public function create(Request $request): Response
    {
        dump($request);

        return $this->render('blog/create.html.twig');
    }

    /**
     * Méthode permettant d'afficher le détail d'un article
     * On définit ici une route paramétré, une route définit avec un ID qui va receptionnée un ID d'un article dans l'URL
     * 
     * @Route("/blog/{id}", name="blog_show")
     */
    public function show(Article $article): Response
    {
        // $repoArticle est un objet issu de la classe ArticleRepository
        //$repoArticle = $this->getDoctrine()->getRepository(Article::class);

        //dump($repoArticle);

        //dump($id);

        // On transmet à la méthode find() de la classe ArticleRepository l'id recupéré dans l'URL et transmit en argument de la fonction show($id) | $id = 3
        // La méthode find() permet de selectionner en BDD un article par son ID
        //$article = $repoArticle->find($id);

        dump($article);

        //dump($id); // id=9
        return $this->render('blog/show.html.twig',[
        'articleTwig' => $article //on envoit sur le template les données selectionnées en BDD, càd les informations d'un article sur l'id de l'url
        ]);
    }

    
}

