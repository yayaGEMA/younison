<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\Article;
use App\Entity\User;
use App\Entity\Comment;
use DateTime;
use Faker;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private $encoder;

    /**
     * Utilisation du constructeur pour récupérer le service de hashage des mots de passe via autowiring
     */
    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }


    public function load(ObjectManager $manager)
    {

        // On instancie le Faker en langue française
        $faker = Faker\Factory::create('fr_FR');


        // Boucle de 20 itérations
        for($i = 1; $i <= 20; $i++){

            // Création d'un nouvel user
            $newUser = new User();

            // Hydratation du nouvel user
            $newUser
                ->setPseudo($faker->userName)
                ->setEmail($faker->email)
                ->setPassword( $this->encoder->encodePassword($newUser, 'Alicedu71@') )
                ->setRegistrationDate($faker->dateTimeBetween('-5years', 'now'))
                ->setIsActivated($faker->boolean)
                ->setActivationToken( $faker->md5 )
            ;

            // Enregistrement du nouvel user auprès de Doctrine
            $manager->persist($newUser);

            // Stockage des comptes de côté pour créer des articles plus bas
            $users[] = $newUser;

        }

        // Boucle de 10 itérations
        for($i = 1; $i <= 15; $i++){

            // Création d'un nouvel article
            $newArticle = new Article();

            // Hydratation du nouvel article
            $newArticle
                ->setTitle($faker->sentence(5))
                ->setContent($faker->paragraph(50))
                ->setPublicationDate($faker->dateTimeBetween('-5years', 'now'))
                ->setAuthor($faker->randomElement($users))
                ->setPicture($faker->file('public/images', 'public/images/articles', false))
                ->setLikes($faker->numberBetween($min = 0, $max = 20000))
            ;

            // Enregistrement du nouvel user auprès de Doctrine
            $manager->persist($newArticle);

             // Stockage des articles de côté pour créer des commentaires plus bas
             $articles[] = $newArticle;

            // Création entre 0 et 50 commentaires aléatoires par article
            $rand = rand(0, 50);

            for($j = 0; $j < $rand; $j++){

                // Création nouveau commentaire
                $newComment = new Comment();

                // Hydratation du commentaire
                $newComment
                    ->setArticle($faker->randomElement($articles))
                    // Date aléatoire entre la publication du dernier commentaire et maintenant
                    ->setPublicationDate($faker->dateTimeBetween( '-1 year' , 'now'))
                    // Auteur aléatoire parmis les comptes créés plus haut
                    ->setAuthor($faker->randomElement($users))
                    ->setContent($faker->paragraph(5))
                    ->setLikes($faker->numberBetween($min = 0, $max = 200))
                ;


                // Persistance du commentaire
                $manager->persist($newComment);

            }

        }


        $manager->flush();
    }
}
