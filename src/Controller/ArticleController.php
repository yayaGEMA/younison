<?php

namespace App\Controller;

use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Article;
use App\Form\ArticleType;
use App\Entity\Comment;
use App\Form\CommentType;
use App\Form\EditArticleType;
use App\Form\EditPhotoType;
use App\Entity\User;
use \DateTime;
use Symfony\Component\Form\FormError;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;


/**
 * @Route("", name="article_")
 */
class ArticleController extends AbstractController
{

    /**
     * @Route("/ajouter/", name="new_article")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function newArticle(Request $request)
    {
        // Création d'un article vide
        $article = new Article();

        // Création d'un nouveau formulaire basé sur "ArticleType", qui hydratera notre article "$article"
        $form = $this->createForm(ArticleType::class, $article);

        // Remplissage du traitement du formulaire avec les données POST (sous forme d'objet $request)
        $form->handleRequest($request);

        // Si le formulaire a été envoyé
        if($form->isSubmitted() && $form->isValid()){

            // Extraction de l'objet de la photo envoyée dans le formulaire
            $picture = $form->get('picture')->getData();

            // Création d'un nouveau nom aléatoire pour la photo avec son extension (récupérée via la méthode guessExtension() )
            $newFileName = md5(time() . rand() . uniqid() ) . '.' . $picture->guessExtension();

            // Déplacement de la photo dans le dossier que l'on avait paramétré dans le fichier services.yaml, avec le nouveau nom qu'on lui a généré
            $picture->move(
                $this->getParameter('app.user.photo.directory'),     // Emplacement de sauvegarde du fichier
                $newFileName    // Nouveau nom du fichier
            );

            $article->setPicture($newFileName);

            // Récupération de l'user actuellement connecté
            $userConnected = $this->getUser();

            // Hydratation de la publicationDate, des likes et de l'auteur de l'article
            $article
                ->setPublicationDate(new DateTime())
                ->setAuthor($userConnected)
                ->setLikes(0)
            ;

            // Récupération du manager général des entités
            $entityManager = $this->getDoctrine()->getManager();

            // Persistance de l'article auprès de Doctrine
            $entityManager->persist($article);

            // Sauvegarder en bdd
            $entityManager->flush();

            // TODO: ajouter un message flash de succès
            $this->addFlash('success', 'Article publié avec succès !');

            // Redirige sur la page des articles
            return $this->redirectToRoute('main');
        }

        // On appelle la vue en lui transmettant l'affichage du formulaire dans une variable "form"
        return $this->render('articles/newArticle.html.twig', [
            'form' => $form->createView()
        ]);

    }

    /**
     * @Route("/articles/", name="list")
     */
    public function articleList(Request $request, PaginatorInterface $paginator)
    {
        // On récupère dans l'url la données GET page (si elle n'existe pas, la valeur retournée par défaut sera la page 1)
        $requestedPage = $request->query->getInt('page', 1);

        if($requestedPage < 1){
            throw new NotFoundHttpException();
        }

        // Récupération du manager des entités
        $entityManager = $this->getDoctrine()->getManager();

        // Création d'une requête pour récupérer les articles en article
        $query = $entityManager->createQuery('SELECT a FROM App\Entity\Article a  ORDER BY a.publicationDate DESC');

        $pageArticles = $paginator->paginate(
            $query,     // Requête de selection des articles en BDD
            $requestedPage,     // Numéro de la page dont on veux les articles
            10      // Nombre d'articles par page
        );

        // Permet de récupérer le ORDER BY de la requête
        $getDirection = $request->query->get('direction');

        // On envoi les articles récupérés à la vue
        return $this->render('articles/articleList.html.twig', [
            'pageArticles' => $pageArticles,
            'getDirection' => $getDirection
        ]);

    }

    /**
     * Page d'affichage d'un article en détail
     *
     * @Route("/article/{slug}/", name="article")
     */
    public function articleView(Article $article, Request $request){

        // Récupération de l'user actuellement connecté
        $userConnected = $this->getUser();

        // Si l'utilisateur n'est pas connecté, appel direct de la vue en lui envoyant l'article à afficher
        // On fait ça pour éviter que le traitement du formulaire en dessous ne soit invoqué par un autre moyen même si le formulaire html est masqué
        if(!$userConnected){
            return $this->render('articles/articleView.html.twig', [
                'article' => $article,
            ]);
        }

        //Création d'un commentaire vide
        $newComment = new Comment();

        //Création d'un formulaire de création de commentaire lié à $newComment
        $commentForm = $this->createForm(CommentType::class, $newComment);

        //liaison des données de requête (POST) avec le formulaire
        $commentForm->handleRequest($request);

        if($commentForm->isSubmitted() && $commentForm->isValid()){
            $newComment
                ->setAuthor($userConnected)  //L'auteur est l'utilisateur connecté
                ->setPublicationDate(new DateTime()) //Date actuelle
                ->setArticle($article) //Lié à l'article actuellement affiché sur la page
                ->setLikes(0) //Initialise le compteur de likes
            ;

            // Sauvegarde du commentaire en base de données via le manager général des entités
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($newComment);
            $entityManager->flush();

            //Message flash de type "success"
            $this->addFlash('success', 'Votre commentaire a été publié avec succès !');

            //suppression des 2 variables contenant le formulaire validé et le commentaire nouvellement crée (pour éviter que le nouveau formulaire soit rempli avec)
            unset($newComment);
            unset($commentForm);

            //création d'un nouveau commentaire vide et de son formulaire lié
            $newComment = new Comment();
            $commentForm = $this->createForm(CommentType::class, $newComment);
        }

        // Appel de la vue en lui envoyant l'article
        return $this->render('articles/articleView.html.twig', [
            'article' => $article,
            'comment' => $newComment,
            'user' => $userConnected,
            'commentForm' => $commentForm->createView()
        ]);
    }

    /**
     * Page user permettant de modifier un article existant via son slug passé dans l'url
     *
     * @Route("/{slug}/modifier/", name="edit")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function articleEdit(Article $article, request $request)
    {

        // Création du formulaire de modification (c'est le même que le formulaire permettant de créer un nouveau article, sauf qu'il sera déjà rempli avec les données de "$article")
        $form = $this->createForm(EditArticleType::class, $article);

        // Liaison des données de requête (POST) avec le formulaire
        $form->handleRequest($request);

        // Si le formulaire est envoyé et n'a pas d'erreur
        if($form->isSubmitted() && $form->isValid()){

            // Sauvegarde des changements faits via le manager général des entités
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();

            // Message flash de type "success"
            $this->addFlash('success', 'Article modifié avec succès !');

            // Redirection vers la page du bien modifié
            return $this->redirectToRoute('article_article', ['slug' => $article->getSlug()]);

        }

        // Appel de la vue en lui envoyant le formulaire à afficher
        return $this->render('articles/editArticle.html.twig', [
            'form' => $form->createView(),
        ]);

    }

    /**
     * Page admin servant à supprimer un article via son id passé dans l'url
     *
     * @Route("/article/suppression/{id}/", name="delete")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function articleDelete(Article $article, Request $request){

        // Si le token CSRF passé dans l'url n'est pas le token valide, message d'erreur
        if(!$this->isCsrfTokenValid('article_delete_'. $article->getId(), $request->query->get('csrf_token'))){

            $this->addFlash('error', 'Token sécurité invalide, veuillez ré-essayer.');

        } else {

            // Suppression de l'article via le manager général des entités
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($article);
            $entityManager->flush();

            // Message flash de type "success" pour indiquer la réussite de la suppression
            $this->addFlash('success', 'Article supprimé avec succès !');

        }

        // Redirection de l'utilisateur sur la liste des articles
        return $this->redirectToRoute('article_list');
    }

   /**
     * Page admin servant à supprimer un commentaire via son id passé dans l'url
     *
     * @Route("/commentaire/suppression/{id}/", name="comment_delete")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function commentDelete(Comment $comment, Request $request){

        // Si le token CSRF passé dans l'url n'est pas le token valide, message d'erreur
        if(!$this->isCsrfTokenValid('comment_delete'. $comment->getId(), $request->query->get('csrf_token'))){

            $this->addFlash('error', 'Token sécurité invalide, veuillez ré-essayer.');

        } else {

            // Suppression du commentaire via le manager général des entités
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($comment);
            $entityManager->flush();

            // Message flash de type "success" pour indiquer la réussite de la suppression
            $this->addFlash('success', 'Le commentaire a été supprimé avec succès!');

        }

        // Redirection de l'utilisateur sur la page détaillée de l'article auquel est/était rattaché le commentaire
        return $this->redirectToRoute('article_article', [
            'slug' => $comment->getArticle()->getSlug(),
        ]);

    }


}