<?php
namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use App\Entity\User;
use \DateTime;
use EasyCorp\Bundle\EasyAdminBundle\Controller\EasyAdminController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Contrôleur spécial servant à modifier le fonctionnement de certaines actions dans easyadmin
 */
class AdminController extends EasyAdminController
{

    private $encoder;

    /**
     * Utilisation du constructeur pour récupérer le service de hashage des mots de passe via autowiring
     */
    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    /**
     * Les nouveaux comptes créés auront leur token d'activation et leur date d'inscription pré-hydratés
     */
    public function createNewUserEntity()
    {
        $user = new User();
        $user
            ->setActivationToken( md5(random_bytes(100)) )
            ->setRegistrationDate( new DateTime() )
        ;
        return $user;
    }

    /**
     * Hashage du mot de passe des nouveaux comptes créés via easyadmin
     */
    public function persistUserEntity(User $user)
    {
        $user->setPassword(
            $this->encoder->encodePassword($user, $user->getPlainPassword())
        );

        parent::persistEntity($user);
    }

    /**
     * Hashage du mot de passe des comptes édités via easyadmin
     */
    public function updateUserEntity(User $user)
    {
        $user->setPassword(
            $this->encoder->encodePassword($user, $user->getPlainPassword())
        );

        parent::updateEntity($user);
    }

    /**
     * Les nouveaux articles créés auront leur date de publication pré-hydratée
     */
    public function createNewArticleEntity()
    {
        $article = new Article();
        $article
            ->setPublicationDate( new DateTime() )
        ;
        return $article;
    }

    /**
     * Les nouveaux commentaires créés auront leur date de publication pré-hydratée
     */
    public function createNewCommentEntity()
    {
        $comment = new Comment();
        $comment
            ->setPublicationDate( new DateTime() )
        ;
        return $comment;
    }

}