<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @UniqueEntity(
 *        fields = {"email"},
 *        message = "Un compte est déjà existant à cette adresse Email !!"
 * )
 */
class User implements UserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(
     *      message = "Veuillez renseigner une adresse Email !"
     * )
     * 
     * @Assert\Email(
     *      message ="Veuillez saisir une adresse Email valide !"
     * )
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255)
     * 
     * @Assert\NotBlank(
     *      message = "Veuillez renseigner un nom d'utilisateur !"
     * )
     * 
     * 
     * @Assert\Length(
     *      min="3",
     *      max="20",
     *      minMessage="Nom d'utilisateur trop court",
     *      maxMessage="Nom d'utilisateur trop long"
     * )
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\EqualTo(
     *          propertyPath="confirm_password",
     *          message="Les mots de passe ne correspondent pas",
     *          groups={"registration"}
     * )
     * 
     * @Assert\NotBlank(
     *      message = "Veuillez renseigner un mot de passe !",
     *      groups={"registration"}
     * )
     * 
     * 
     */
    private $password;

    /**
     * Cette propriété receptionne une valeur mais n'est pas stockée en BDD, donc  pas d'annotation ORM
     * 
     * @Assert\EqualTo(
     *      propertyPath="password",
     *      message="Les mots de passe ne correspondent pas",
     *      groups={"registration"}
     * )
     * 
     * 
     * @Assert\NotBlank(
     * 
     *      message = "Veuillez Confirmer votre mot de passe !",
     *      groups={"registration"}
     * )
     */
    public $confirm_password;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    // Pour encoder le mot de passe, l'entité USer doit implémenter (similaire de l'héritage) l'interface UserInterface
    // Cette interface contient des méthodes abstraites que nous sommes obligé de déclarer 
    // méthodes obligatoires :  getUsername(), getPassword(), eraseCredentials(), getSalt() et getRoles()

    //Cette méthode est uniquement destinnée a nettoyer les mots de passe en text brut éventuellement stockés
    public function eraseCredentials()
    {

    }

    // Renvoi la chaine de caractère non encodé que l'utlisateur a saisi, qui est utilisé à l'origine pour, encoder le mot de passe
    public function getSalt()
    {

    }

    // Cette fonction renvoi un tableau de chaine de caractères 
    // Renvoi les rôles accordés à l'utilisateur
    public function getRoles()
    {
        return $this->roles;
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }
}
