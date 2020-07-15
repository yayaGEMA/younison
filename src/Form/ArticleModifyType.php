<?php

namespace App\Form;

use App\Entity\Article;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArticleModifyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            // Champ titre
            ->add('title', TextType::class, [
                'label' => 'Titre de l\'article',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Merci de renseigner un titre'
                    ]),

                    new Length([
                        'min' => 3,
                        'minMessage' => 'Le titre doit contenir au moins {{ limit }} caractères',
                        'max' => 400,
                        'maxMessage' => 'Le titre doit contenir au maximum {{ limit }} caractères'
                    ]),
                ]
            ])

            // Champ contenu
            ->add('content', CKEditorType::class, [
                'label' => 'Contenu de l\'article à modifier',
                'purify_html' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Merci de renseigner un contenu'
                    ]),
                    new Length([
                        'min' => 10,
                        'minMessage' => 'Le contenu de l\'article doit contenir au moins {{ limit }} caractères',
                        'max' => 20000,
                        'maxMessage' => 'Le contenu de l\'article doit contenir au maximum {{ limit }} caractères'
                    ]),
                ]
            ])

            //Champ URI Spotify
            ->add('spotifyUri', TextType::class, [
                'label' => 'URI Spotify à ajouter',
                'attr' => [
                    'placeholder' => 'spotify:track:6h9idcOWtFv4Nr1oCiMSEm'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Merci d\'insérer une URI'
                    ]),
                    new Regex([
                        'pattern' => "/^spotify:[a-z]{5,8}:[a-z0-9]{22}$/i",
                        'message' => 'Votre URI est invalide'
                    ])

                ]
            ])

            ->add('save', SubmitType::class, [
                'label' => 'Envoyer',
                'attr' => [
                    'class' => 'btn btn-primary col-12'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
            'novalidate' => 'novalidate'
        ]);
    }
}
