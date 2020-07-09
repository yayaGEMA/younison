<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder

            // Champ email
            ->add('email', EmailType::class, [
                'label' => 'Adresse Email',
                'constraints' => [
                    new Email([
                        'message' => 'L\'adresse email {{ value }} n\'est pas une adresse valide'
                    ]),
                    new NotBlank([
                        'message' => 'Merci de renseigner une adresse email'
                    ])
                ],
            ])

            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Le mot de passe ne correspond pas à sa confirmation',
                'first_options' => [
                    'label' => 'Mot de passe',
                ],
                'second_options' => [
                    'label' => 'Confirmation du mot de passe'
                ],
                'mapped' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Merci de renseigner un mot de passe'
                    ]),
                    new Length([
                        'min' => 8,
                        'minMessage' => 'Votre mot de passe doit contenir au moins {{ limit }} caractères',
                        'max' => 60,
                        'maxMessage' => 'Mot de passe trop grand'
                    ]),
                    new Regex([
                        'pattern' => "/(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[ !\"\#\$%&\'\(\)*+,\-.\/:;<=>?@[\\^\]_`\{|\}~])^.{8,60}$/",
                        'message' => 'Votre mot de passe doit contenir obligatoirement une minuscule, une majuscule, un chiffre et un caractère spécial'
                    ])
                ]
            ])

            //Champ pseudo
            ->add('pseudo', TextType::class, [
                'label' => 'Pseudonyme',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Merci de renseigner un pseudonyme'
                    ]),
                    new Length([
                        'min' => 2,
                        'minMessage' => 'Votre pseudonyme doit contenir au moins {{ limit }} caractères',
                        'max' => 40,
                        'maxMessage' => 'Votre pseudonyme doit contenir au maximum {{ limit }} caractères'
                    ]),
                ]
            ])

            // Bouton de validation
            ->add('save', SubmitType::class, [
                'label' => 'Créer mon compte',
                'attr' => [
                    'class' => 'btn btn-outline-primary col-12'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'attr' => [
                'novalidate' => 'novalidate'
            ]
        ]);
    }
}
