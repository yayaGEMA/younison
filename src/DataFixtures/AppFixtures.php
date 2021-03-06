<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\Article;
use App\Entity\User;
use App\Entity\Comment;
use App\Entity\ArticleLike;
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

        // Array avec des URI Spotify
        $musicArray = [
            'spotify:track:2EqlS6tkEnglzr7tkKAAYD',
            'spotify:track:6rlNCgN2JwbTNVQgwLp5Pl',
            'spotify:playlist:60vmRw72rGx4SFcWcS8VmB',
            'spotify:track:00oZhqZIQfL9P5CjOP6JsO',
            'spotify:album:2tVVN2fdC4BzqBMTvpJ7Bp',
            'spotify:album:3Rz6kF8eGqrDOEteo5YsBj',
            'spotify:playlist:5RtIoZHMy9nfJ8mCNGceFy',
            'spotify:playlist:37i9dQZF1DX82CY3GzF2m6',
            'spotify:playlist:37i9dQZF1DZ06evO4ohLfG'
        ];


        // Boucle de 20 itérations
        for($i = 1; $i <= 20; $i++){

            // Création d'un nouvel user
            $newUser = new User();

            // Hydratation du nouvel user
            $newUser
                ->setPseudo($faker->userName)
                ->setEmail($faker->email)
                ->setPassword( $this->encoder->encodePassword($newUser, 'Alicedu71@') )
                ->setProfilPic($faker->file('public/images/profilpicsBank', 'public/images/users', false))
                ->setRegistrationDate($faker->dateTimeBetween('-5years', 'now'))
            ;

            // Enregistrement du nouvel user auprès de Doctrine
            $manager->persist($newUser);

            // Stockage des comptes de côté pour créer des articles plus bas
            $users[] = $newUser;

        }

        // Création du nombre de likes
        $numberOfLikes = 0;

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
                ->setLikesCounter($numberOfLikes)
                ->setSpotifyUri($faker->randomElement($musicArray))
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

            // Création entre 0 et 50 likes aléatoires par article
            for($k = 0; $k < $rand; $k++){
                $like = new ArticleLike();
                $like
                    ->setArticle($newArticle)
                    ->setUser($faker->randomElement($users))
                ;

                $newArticle->setLikesCounter(++$numberOfLikes);

                // Persistance du commentaire
                $manager->persist($like);
            }

        }


        $manager->flush();
    }
}
