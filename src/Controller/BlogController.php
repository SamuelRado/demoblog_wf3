<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use App\Entity\Contact;
use App\Form\ArticleType;
use App\Form\ContactType;
use App\Form\PostCommentType;
use App\Notification\ContactNotification;
use App\Repository\ArticleRepository;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BlogController extends AbstractController
{
    /**
     * @Route("/home", name="home")
     */
    public function home()
    {
        return $this->render("blog/home.html.twig", [
            'title' => 'Bienvenue sur le blog',
            'age' => 35
        ]);
    }

    /**
     * @Route("/blog", name="app_blog")
     */
    public function index(ArticleRepository $repo): Response
    {
        $articles = $repo->findAll();
        return $this->render('blog/index.html.twig', [
            'articles' => $articles
        ]);
    }

    /**
     * @Route("/blog/show/{id}", name="blog_show")
     */
    public function show($id, ArticleRepository $repo, Request $request, EntityManagerInterface $manager)
    {
        $article = $repo->find($id);
        if (!$article)
            return $this->render("404.html.twig");

        $comment = new Comment;
        $form = $this->createForm(PostCommentType::class, $comment);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $comment->setCreatedAt(new \DateTime())
                    ->setAuthor($this->getUser())   // $this->getUser() récupère l'utilisateur connecté
                    ->setArticle($article);
            $manager->persist($comment);
            $manager->flush();
            return $this->redirectToRoute('blog_show', [
                'id' => $article->getId()
            ]);
        }

        return $this->renderForm("blog/show.html.twig", [
            'article' => $article,
            'formComment' => $form
        ]);
    }

    /**
     * @Route("/blog/new", name="blog_create")
     * @Route("/blog/edit/{id}", name="blog_edit")
     */
    public function form(Request $request, EntityManagerInterface $manager, Article $article = null)
    {
        // la classe Request contient toutes les données véhiculées par les superglobales

        if(!$article)
        {
            $article = new Article;
            $article->setCreatedAt(new \DateTime());    // ajout de la date seulement à l'insertion
        }
        $form = $this->createForm(ArticleType::class, $article);
        dump($request);
        $form->handleRequest($request);
        // handleRequest() permet d'insérer les données du formulaire dans l'objet $article
        // elle permet aussi de faire des vérif sur le formulaire : quelle méthode ? est-ce que les champs sont remplis ? etc

        if($form->isSubmitted() && $form->isValid())
        {
            $manager->persist($article);    // prépare l'insertion
            $manager->flush();  // exécute la requête
            return $this->redirectToRoute('blog_show', [
                'id' => $article->getId()
            ]);
        }
        return $this->renderForm("blog/form.html.twig", [
            'formArticle' => $form,
            'editMode' => $article->getId() !== null
        ]);
    }

    /**
     * @Route("/profile", name="profile")
     */
    public function profile(CommentRepository $repo)
    {
        // $comments = $this->getUser()->getComments();
        // dd($this->getUser());
        $comments = $repo->findBy(['author' => $this->getUser()]);
        return $this->render("blog/profile.html.twig", [
            'comments' => $comments
        ]);
    }

    /**
     * @Route("/blog/contact", name="blog_contact")
     */
    public function contact(Request $request, EntityManagerInterface $manager, ContactNotification $cn)
    {
        $contact = new Contact;
        $form = $this->createForm(ContactType::class, $contact);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $manager->persist($contact);
            $manager->flush();
            $cn->notify($contact);
            $this->addFlash('success', 'Votre email a bien été envoyé !');
            // addFlash() permet de créer des msg de notification stockés dans la session de l'utilisateur
            // il est supprimé après affichage
            return $this->redirectToRoute('blog_contact');
        }

        return $this->renderForm("blog/contact.html.twig", [
            'formContact' => $form
        ]);
    }
}
