<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\Article;
use App\Entity\User;
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

            // Stockage du compte de côté pour créer des articles plus bas
            $users[] = $newUser;

        }

        // Boucle de 10 itérations
        for($i = 1; $i <= 10; $i++){

            // Création d'un nouvel article
            $newArticle = new Article();

            // Hydratation du nouvel article
            $newArticle
                ->setTitle($faker->sentence(10))
                ->setContent($faker->paragraph(10))
                ->setPublicationDate($faker->dateTimeBetween('-5years', 'now'))
                ->setAuthor($faker->randomElement($users))
                ->setPicture($faker->image('public/images/articles', false))
                ->setLikes($faker->numberBetween($min = 0, $max = 20000))
            ;

            // Enregistrement du nouvel user auprès de Doctrine
            $manager->persist($newArticle);

        }


        $manager->flush();
    }
}
