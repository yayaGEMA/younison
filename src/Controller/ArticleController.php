<?php

namespace App\Controller;

use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Article;
use App\Form\ArticleType;
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
            $em = $this->getDoctrine()->getManager();

            // Persistance de l'article auprès de Doctrine
            $em->persist($article);

            // Sauvegarder en bdd
            $em->flush();

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
     * @Route("/articles/", name="article_list")
     */
    public function articleList(Request $request, PaginatorInterface $paginator)
    {

        // Récupération du manager des entités
        $em = $this->getDoctrine()->getManager();

        // Création d'une requête pour récupérer les articles en article
        $query = $em->createQuery('SELECT a FROM App\Entity\Article a  ORDER BY a.publicationDate DESC');

        // On stocke dans $pageArticles les 10 articles de la page demandée dans l'URL
        $articles = $paginator->paginate(
            $query,     // Requête de selection des articles en BDD
        );

        // On envoi les articles récupérés à la vue
        return $this->render('articles/articleList.html.twig', [
            'articles' => $articles
        ]);

    }

    /**
     * Page d'affichage d'une annonce en détail
     *
     * @Route("/article/{slug}/", name="article")
     */
    public function articleView(Article $article, Request $request){

        // Récupération de l'user actuellement connecté
        $userConnected = $this->getUser();

        // Appel de la vue en lui envoyant le article
        return $this->render('articles/articleView.html.twig', [
            'article' => $article,
            'user' => $userConnected,
        ]);
    }

    /**
     * Page user permettant de modifier une annonce existant via son slug passé dans l'url
     *
     * @Route("/{slug}/modifier/", name="article_edit")
     * @Security("article.isAuthor(user)")
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
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            // Message flash de type "success"
            $this->addFlash('success', 'Article modifié avec succès !');

            // Redirection vers la page du bien modifié
            return $this->redirectToRoute('article_view', ['slug' => $article->getSlug()]);

        }

        // Appel de la vue en lui envoyant le formulaire à afficher
        return $this->render('articles/editArticle.html.twig', [
            'form' => $form->createView(),
        ]);

    }

}