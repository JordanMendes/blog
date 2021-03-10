<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
    public function index(): Response
    {
        return $this->render('blog/index.html.twig', [
            'title' => 'Liste des Articles',
        ]);
    }

    /**
     * Méthode permettant d'afficher le détail d'un article
     * 
     * @Route("/blog/12", name="blog_show")
     */
    public function show(): Response
    {
        return $this->render('blog/show.html.twig');
    }
}
