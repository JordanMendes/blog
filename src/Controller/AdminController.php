<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Article;
use App\Entity\Comment;
use App\Entity\Category;
use App\Form\ArticleFormType;
use App\Form\CommentFormType;
use App\Form\CategoryFormType;
use App\Repository\UserRepository;
use App\Repository\ArticleRepository;
use App\Repository\CommentRepository;
use App\Repository\CategoryRepository;
use App\Form\AdminRegistrationFormType;
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
    public function adminCategory(EntityManagerInterface $manager, CategoryRepository $repoCategory, Category $category = null): Response
    {
        $colonnes = $manager->getClassMetadata(Category::class)->getFieldNames();

        dump($colonnes);

        // Si la variable $category retourne TRUE, cela veut dire qu'elle contient une catégorie de la BDD , alors on entre dans le IF et on tente d'executer la suppression
        if($category)
        {
            // Nous avons une relation entre la table Article et Category et une contrainte d'intégrité en RESTRICT
            // Donc ne pourrons pas supprimer la catégorie si 1 article lui est toujours associé
            // getarticles( de l'entité Category retourne tout les articles associés à la catégorie (relation bi-directionnelle))
            // Si getArticles() retourne un résultat vide, cela veut dire qu'il n'y a plus aucun article associé à la catégorie, nous pouvons donc la supprimer
            if($category->getArticles()->isEmpty())
            {
                $manager->remove($category);
                $manager->flush();
            }
            else // Sinon dans tout les autres cas, des articles sont toujours associés à la catégorie, on affiche un message erreur utilisateur
            {
                $this->addFlash('danger', "Il n'est pas possible de supprimer la catégorie : articles affiliées à celle-ci");
            }

            return $this->redirectToRoute('admin_category');
        }

        $categoryBdd = $repoCategory->findAll();

        dump($categoryBdd);

        return $this->render('admin/admin_category.html.twig', [
            'colonnes' => $colonnes,
            'categoryBdd' => $categoryBdd
        ]);
    }

    /**
     * @Route("/admin/category/new", name="admin_new_category")
     * @Route("/admin/category{id}/edit", name="admin_edit_category")
     * 
     */
    public function adminFormCategory(Request $request, EntityManagerInterface $manager, Category $category = null):Response
    {
        /*
            Insertion d'une categorie en BDD : 
            1. Créer une classe permettant de générer un forumlaire correspondant à l'entité Category (make:form)
            2. dans le controller, faites en sorte d'importer et de créer le formulaire, en le reliant à l'entité
            3. Envoyé le formulaire sur le template (render) et l'afficher en front 
            4. Récupérer et envoyer les données de $_POST dans le bonne entité à la valodation du formulaire (handleRequest + $request)
            5. Générer et executer la requete d'insertion à la validation du formulaire ($manager + persist + flush)
        */

        // Si l'objet entité $category ne possède pas d'id, cela veut dire que nous sommes sur la route '/admin/category/new', que nous souhaitons créer une nouvelle catégorie, alors on entre dans la condition IF
        // Si l'objet entité $category possède un id, cela veut dire que nous sommes sur la route "/admin/category/{id}/edit", l'id envoyé dans l'URL a été selctionné en BDD, nous souhaitons modifier la catégorie existante
        if(!$category)
        {
            $category = new Category; 
        }

        $form = $this->createForm(CategoryFormType::class, $category, [
            'validation_groups' => ['category']
        ]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            if(!$category->getId())
                $message = "La catégorie " . $category->getTitle() ."a été enregistrée avec succes !";
            else
                $message = "La catégorie " . $category->getTitle() ." a été modifiée avec succes !";

            $manager->persist($category); 
            $manager->flush(); 

            $this->addFlash('success', $message);


           return $this->redirectToRoute('admin_category');
        }

        return $this->render('admin/admin_form_category.html.twig', [
            'formCategory' => $form->createView(), 
        ]);
    }
    /**
     * Méthode permettant d'afficher tout les commentaire des articles stockés en BDD 
     * Méthode permettat de suprimer un commentaire en BDD
     * 
     * @Route ("/admin/comments", name="admin_comments")
     * @Route ("/admin/comment/{id}/remove", name="admin_remove_comment")
     */
    public function adminComment(EntityManagerInterface $manager, CommentRepository $repoComment, Comment $comment = null): Response
    {
        $colonnes = $manager->getClassMetadata(Comment::class)->getFieldNames();

        dump($colonnes);

        $commentsBdd = $repoComment->findAll();

        dump($commentsBdd);

        if($comment)
        {
            // On stock l'id du commentaire a supprimer avant d'executer la requete DELETE afin d'injecter l'id du commentaire dans le message de validation
            $id = $comment->getId();
            $auteur = $comment->getAuthor(); // on stocke l'auteur du commentaire a supprimé 

            $date = $comment->getCreatedAt();
            $dateFormat = $date->format('d/m/Y à H:i:s'); // format() --> on formate la date et l'heure

            $manager->remove($comment); // on prépare et on garde en mémoire la requete de suppression (DELETE)
            $manager->flush(); // on execute la reque de suppression

            // On stock un message de validation en session
            $this->addFlash('success', "Le commentaire n°$id posté par l'auteur $auteur le $dateFormat a été supprimé avec succes !");

            // Apres la suppression, on redirige l'utilisateur vers l'affichage des commentaires
            return $this->redirectToRoute('admin_comments');
        }

        return $this->render('admin/admin_comments.html.twig', [
            'colonnes' => $colonnes,
            'commentsBdd' => $commentsBdd
        ]);
    }

    /**
     * Méthode permettant de modifier un commentaire en BDD
     * 
     * @Route("/admin/comment/{id}/edit", name="admin_edit_comment")
     * 
     */
    public function editComment(Comment $comment, EntityManagerInterface $manager, Request $request): Response
    {
        /*
            1. Importer le formulaire des commentaires dans le controller et le relié à l'entité
            2. Faites en sorte de récupérer les bonnes valeurs du commentaire à modifier dans les attributs 'value' du formulaire en fonction de l'id commentaire transmit dans l'URL
            3. Réaliser le traitement permettant de génrer la modification du commentaire à la validation du formulaire
        */
        dump($comment);

        $formComment = $this->createForm(CommentFormType::class, $comment);

        $formComment->handleRequest($request);

        if($formComment->isSubmitted() && $formComment->isValid())
        {
            $id = $comment->getId();
            $auteur = $comment->getAuthor();
            $date = $comment->getCreatedAt();
            $dateFormat = $date->format('d/m/Y à H:i:s');

            $manager->persist($comment); // on prépare la requete de modification UPDATE
            $manager->flush(); // on execute la requete UPDATE

            // On définit un message de validation
            $this->addFlash("success", "Le commentaire n°$id posté par $auteur le $dateFormat a été modifié avec succès !");

            // on redirige l'unternaute apres la modification vers l'affichage des commentaires en BackOffice
            return $this->redirectToRoute('admin_comments');
        }

        return $this->render('admin/admin_edit_comments.html.twig', [
            'formComment' => $formComment->createView()
        ]);
    }
    /**
     * @Route("/admin/users", name="admin_users")
     * @Route("/admin/user/{id}/remove", name="admin_remove_user")
     * @Route("/admin/user/{id}/edit", name="admin_edit_user")
     */
    public function adminUsers(EntityManagerInterface $manager, UserRepository $repoUser, User $user = null): Response
    {
        /*
            Affichage de la table SQL user sous forme de tableau HTML : 
                1. Récupérer le nom des champs / colonnes de la table 'user' afin de les afficher dans les entêtes du tableau HTML 
                2. Selectionner en BDD toute la table 'user'
                3. Afficher le contenu de la table sur le template, faites en sorte de ne pas avoir le champ 'password' affiché dans le tableau
                4. Prévoir des liens : modification / suppression
        */
        
        $colonnes = $manager->getClassMetadata(User::class)->getFieldNames();
        
        

        $usersBdd = $repoUser->findAll();

        dump($user);


        if($user)
        {
            $manager->remove($user);

            $manager->flush();

            $this->addFlash('success', "L'utilisateur a bien été supprmé avec succes !");

            return $this->redirectToRoute('admin_users');
        }

        return $this->render('admin/admin_users.html.twig', [
            'colonnes' => $colonnes,
            'usersBdd' => $usersBdd
        ]);
    }

   /**
    *Méthode permettant de  
    *
    * @Route("/admin/user/{id}/edit", name="admin_edit_user")
    */
    public function adminUserEdit(USER $user, EntityManagerInterface $manager, Request $request): Response
    {
        dump($user);

        $formUser = $this->createForm(AdminRegistrationFormType::class, $user);

        $formUser->handleRequest($request);

        if($formUser->isSubmitted() && $formUser->isValid())
        {
            $id = $user->getId();
            $nom = $user->getUsername();

            $manager->persist($user);
            $manager->flush();

            $this->addFlash('success', "L'utilisateur $nom ID n°$id a été modifié avec succes !");

            return $this->redirectToRoute('admin_users');
        }

        return $this->render('admin/admin_edit_user.html.twig', [
            'formUser' => $formUser->createView()
        ]);
    }
}

