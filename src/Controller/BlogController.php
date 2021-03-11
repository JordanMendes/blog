<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleFormType;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
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
     * @Route("/blog/{id}/edit", name="blog_edit")
     */
    public function create(Article $articleCreate = null, Request $request, EntityManagerInterface $manager): Response
    {
        dump($articleCreate);

        // Si la variable $articleCreate N'EST PAS, si elle ne contient aucun article de la BDD, cela veut dire nous avons envoyé la route '/blog/new', c'est une insertion, on entre dans le IF et on crée une nouvelle instance de l'entité Article, création d'un nouvel article
        // Si la variable $articleCreate contient un article de la BDD, cela veut dire que nous avons envoyé la route '/blog/id/edit', c'est une modifiction d'article, on entre pas dans le IF
        if(!$articleCreate)
        {
            $articleCreate = new Article; // setTitle($_POST['title])
        }

        // $request permet de stocker les données des superglobales , la propriété $request->request permet de stocker les données véhiculées par un formulaire ($_POST), ici on compte si il y a données qui on été saisie dans le formulaire 
        // if($request->request->count() > 0)
        // {
        //     // Pour insérer dans la table Article, nous devons instancier un objet issu de la classe entité Article, qui est lié à la table SQL Article
        //     $articleCreate = new Article; 

        //     // On renseigne tout les setteurs de l'objet avec en arguments les données du formulaire ($_POST)
        //     // $articleCreate->setTitle($request->request->get('title'))
        //     //               ->setContent($request->request->get('content'))
        //     //               ->setImage($request->request->get('image'))
        //     //               ->setCreatedAt(new \DateTime);

        //     dump($articleCreate);   // On observer que l'objet entité Article $articleCrate, les propriétés contiennent bien les données du formulaire

        //     // On fait appel au manager afin de pouvoir executer une isertion en BDD
        //     $manager->persist($articleCreate); // on prépare et grade en mémoire l'insertion
        //     $manager->flush(); // on execute l'insertion

        //     // Après l'insertion, on redirige l'internaute vers le détail de l'article qui vient d'être inséré en BDD
        //     // Cela correspond à la route 'blog_show', mais c'est une route paramétrée qui attend un ID dans l'URL
        //     // En 2ème argument de redirectToRoute, nous transmettons l'ID de l'article qui vient d'être inséré en BDD
        //     return $this->redirectToRoute('blog_show', [
        //         'id' => $articleCreate->getId()
        //     ]);
        // }
        //$articleCreate = new Article; // setTitle($_POST['title'])

        // Ici nous renseignons le setter de l'objet et Symfony est capable automatiquemnt d'envoyer les valeurs de l'entité directement dans les attributs 'value' du formulaire, étant donné que l'entité $articleCreate est relié au formulaire
        // $articleCreate->setTitle("Titre à la con")
        //               ->setContent("Contenu à la con");

        $form = $this->createForm(ArticleFormType::class, $articleCreate);

        // $articleCreate = new Article;

        // createFormBuilder() méthode de Symfony qui permet de générer un formulaire permettant de remplir une entité $articleCreate
        // $form = $this->createFormBuilder($articleCreate)
        //              ->add('title')// add() méthode permettant de générer des champs de formulaire
        //              ->add('content')
        //              ->add('image')
        //              ->getForm(); // permet de valider le formulaire
        
        $form = $this->createForm(ArticleFormType::class, $articleCreate);

        // On pioche dans le formulaire la méthode handleRequest() qui permet de récupérer chaque données saisie dans le formulaire ($request) 
        // et de les bindé, de les transmettre directement dans les bons setteurs de mon entité $articleCreate 
        // $_POST['title'] --> setTitle($_POST['title'])
        $form->handleRequest($request);

        dump($articleCreate);
        
        // Si le formulaire a bien été soumit et que chaque valeur du formulaire ont bien été transmises dans les bon setter de l'entité (isValid()), 
        // alors on entre dans le IF et on génère l'insertion
        if($form->isSubmitted() && $form->isValid())
        {
            // On appel le setter de la date, puisque nous n'avons pas de champs date dans le formulaire
            if(!$articleCreate->getId())
            {
                $articleCreate->setCreatedAt(new \DateTime);
            }

            $manager->persist($articleCreate); // on appel le manager pour préparer la requete d'insertion et la garder en mémoire
            $manager->flush(); // on execute véritablement la requete d'insertion en BDD

            return $this->redirectToRoute('blog_show', [
                'id'=> $articleCreate->getId()
            ]);
        }

        return $this->render('blog/create.html.twig', [
            'formArticle' => $form->createView(), // on transmet sur le template le formulaire, createView() retourne un petit objet qui représente l'affichage du formulaire , on le récupère sur le template create.html.twig 
            'editMode' => $articleCreate->getId() // cela permettra dans le template de savoir si l'article possède un ID ou non , si c'est une insertion ou une modification 
        ]);
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

