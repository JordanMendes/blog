<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class AdminRegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email')
            ->add('username')
            // CollectionType : on définit un champ CollectionType car dans la BDD est stocké un array/json
            ->add('roles', CollectionType::class, [
                'label_format' => 'Role utilisateur', 
                'entry_type' => ChoiceType::class, // On définit le champ comme une liste déroulante
                'entry_options' => [    // on définit les valeur des balises <option></option du selecteur
                    'choices' => [
                        'Utilisateur' => 'ROLE_USER',
                        'Administrateur' => 'ROLE_ADMIN'
                        // 'Utilisateur' --> contenu de la balise <option></option>
                        // 'ROLE_USER' --> dans l'attribut value <option value="ROLE_USER">Utilisateur</option>
                    ]
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
