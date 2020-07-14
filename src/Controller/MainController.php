<?php

namespace App\Controller;

use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Article;
use App\Repository\ArticleRepository;
use Symfony\Component\Form\FormError;
use App\Form\EditPhotoType;



class MainController extends AbstractController
{
    /**
     * @Route("/", name="main")
     */
    public function index()
    {
        // Récupération du repository des articles
        $articleRepo = $this->getDoctrine()->getRepository(Article::class);

        // On demande au repository de nous donner les articles les plus récents
        $indexArticles = $articleRepo->findSevenLatest();

        return $this->render('main/index.html.twig', [
            'index_articles' => $indexArticles
        ]);
    }
    /**
     * @Route("/admin", name="admin")
     */
    public function admin()
    {
        return $this->render('main/admin/');
    }

    /**
     * Page de profil
     *
     * @Route("/mon-profil/", name="profil")
     * @Security("is_granted('ROLE_USER')")
     */
    public function profil(Request $request)
    {
        // Création du formulaire de changement de photo
        $form = $this->createForm(EditPhotoType::class);

        // Liaison des données de requête (POST) avec le formulaire
        $form->handleRequest($request);

        // Si le formulaire a été envoyé et s'il ne contient pas d'erreur
        if($form->isSubmitted() && $form->isValid()){

            // Récupération du champ photo dans le formulaire
            $photo = $form->get('photo')->getData();

            // Si l'utilisateur a déjà une photo de profil, on la supprime
            if($this->getUser()->getProfilPic() != null){

                // Suppression de l'ancienne photo
                unlink($this->getParameter('app.user.photo.directory') . $this->getUser()->getProfilPic());
            }

            // Création d'un nouveau nom de fichier pour la nouvelle photo (boucle jusqu'à trouver un nom pas déjà utilisé)
            do{
                // guessExtension() permet de récupérer la vrai extension du fichier, calculée par rapport à son vrai type MIME
                $newFileName = md5( $this->getUser()->getId() . random_bytes(100) ) . '.' . $photo->guessExtension();

            } while(file_exists($this->getParameter('app.user.photo.directory') . $newFileName));

            // Changement du nom de la photo stockée dans l'utilisateur connecté
            $this->getUser()->setProfilPic($newFileName);

            // Application de ce changement dans la base de données via le manager général des entités
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            // Déplacement physique de l'image dans le dossier paramétré dans le paramètre "app.user.photo.directory" dans le fichier config/services.yaml
            $photo->move(
                $this->getParameter('app.user.photo.directory'),
                $newFileName
            );

            // Message flash de type "success"
            $this->addFlash('success', 'Photo de profil modifiée avec succès !');

            // Redirection de l'utilisateur vers la page de profil
            return $this->redirectToRoute('profil');

        }

        // Appel de la vue en lui envoyant le formulaire à afficher
        return $this->render('main/profil.html.twig', [
            'form' => $form->createView()
        ]);

    }

}