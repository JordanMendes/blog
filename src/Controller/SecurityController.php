<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class SecurityController extends AbstractController
{
    /**
     * @Route("/inscription", name="security_registration")
     */
    public function registration(Request $request, EntityManagerInterface $manager, UserPasswordEncoderInterface $encoder): Response
    {
        // On crée un nouvel exemplaire de l'entité 'User' afin de pouvoir remplir l'objet via le formulaire et de l'insérer en BDD
        $user = new USER;
        

        // On execute la méthode createForm() du SecurityController afin de créer un formulaire par rapport à la classe RegistrationFormType
        // déstiné à remplir les setter de l'objet entité $user 
        $formRegistration = $this->createForm(RegistrationFormType::class, $user, [
            'validation_groups' => ['registration']
        ]);
        // Nous définissions un groupe de validation de contraites afin qu'elles ne soient prise en compte uniquement lors de l'inscription et non lors de la modification dans le BackOffice

        dump($request);

        // handleRequest() : méthode Symfony qui permet à la validation du formulaire, de remplir l'objet entity $user et d'envoyer les données du formulaire dans les bons setter et propriétés de l'entité $user
        $formRegistration->handleRequest($request);

        dump($user);

        if($formRegistration->isSubmitted() && $formRegistration->isValid())
        {
            // SI le formulaire a bien été validé (isSubmitted) et que chaque donnée saisie ont bien été transmise aux bon setter de l'objet (isValid), alors on entre dans le IF

            $hash = $encoder->encodePassword($user, $user->getPassword());

            //dump($hash);

            // On affecte le mot de passe haché directement à l'entité, au setter de l'objet
            $user->setPassword($hash);
            // Pour chaque nouvelle inscription, l'utilisateur aura par défaut le ROLE_USER
            $user->setRoles(["ROLE_USER"]);

            //dump($user);

            $manager->persist($user);// préparation et mise en mémoire de la requete INSERT SQL
            $manager->flush();// execution de la requete SQL

            // On stock un message de validation en session
            $this->addFlash('success', "Félicitations !! Votre compte a bien été validé ! vous pouvez dès à présent vous connecter");

            // Apres l'insertion de l'utilisateur en BDD, on redirige vers le template de connexion
            return $this->redirectToRoute('security_login');
        }

        return $this->render('security/registration.html.twig', [
            'FormRegistration' => $formRegistration->createView() // on envoi le formulaire sur le template afin de pouvoir l'afficher en front 
        ]);
    }

    /**
     * Méthode permettant de se connecter au blog
     * AuthenticationUtils permet de récupérer le dernier Email saisie au moment de la connexion
     * AuthenticationUtils permet de récupérer les messages d'erreurs en cas de mauvaise connexion
     * 
     * 
     * @Route("/connexion", name="security_login")
     * 
     * 
     */
    public function login(AuthenticationUtils $authenticationUtils):Response
    {
        // Récupération du message d'erreur en cas de mauvaise connexion
        $error = $authenticationUtils->getlastAuthenticationError();

        // Récuperation du denier username (email) saisi par l'internaute dans le formulaire de connexion en cas d'erreur
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'error' => $error,
            'lastUsername' => $lastUsername
        ]);
    }

    /**
     * Méthode permettant de se déconnecter, pas de réponse, nous avons juste besion de la route
     * 
     * @Route("/deconnexion", name="security_logout")
     * 
     */
    public function logout()
    {
        //cette méthode ne retourne rien, il nous suffit d'avoir une route pour se deconnecter
    }
}   
