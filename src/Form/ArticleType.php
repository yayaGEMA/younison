<?php

namespace App\Form;

use App\Entity\Article;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\Extension\Core\Type\TransformationFailureExtension;
use Symfony\Component\Form\Extension\HttpFoundation\Type\FormTypeHttpFoundationExtension;
use Symfony\Component\Form\Extension\Validator\Type\FormTypeValidatorExtension;
use Symfony\Component\Form\Extension\Validator\Type\UploadValidatorExtension;
use Symfony\Component\Form\Extension\Csrf\Type\FormTypeCsrfExtension;
use Symfony\Component\Form\Extension\DataCollector\Type\DataCollectorTypeExtension;
use EasyCorp\Bundle\EasyAdminBundle\Form\Extension\EasyAdminExtension;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;


class ArticleType extends AbstractType
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
                'label' => 'Description (soyez le plus exhaustif possible)',
                'purify_html' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Merci de renseigner une description'
                    ]),
                    new Length([
                        'min' => 10,
                        'minMessage' => 'La descritpion doit contenir au moins {{ limit }} caractères',
                        'max' => 20000,
                        'maxMessage' => 'La description doit contenir au maximum {{ limit }} caractères'
                    ]),
                ]
            ])

            // Champ photo
            ->add('picture', FileType::class, [
                'label' => 'Sélectionnez une nouvelle photo',
                'attr' => [
                    'accept' => 'image/jpeg, image/png'
                ],
                'constraints' => [
                    new File([
                        'maxSize' => '1M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/jpg',
                        ],
                        'mimeTypesMessage' => 'L\'image doit être de type jpeg ou png',
                        'maxSizeMessage' => 'Fichier trop volumineux ({{ size }} {{ suffix }}). La taille maximum autorisée est {{ limit }}{{ suffix }}',
                    ]),
                    new NotBlank([
                        'message' => 'Vous devez sélectionner un fichier',
                    ])
                ]
            ])

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
                    'class' => 'btn btn-outline-primary col-12'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
            'attr' => [
                'novalidate' => 'novalidate'
            ]
        ]);
    }
}