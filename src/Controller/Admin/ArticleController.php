<?php

namespace App\Controller\Admin;

use App\Entity\Article;
use App\Entity\User;
use App\Form\ArticleForm;
use App\Repository\ArticleRepository;
use App\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/article')]
final class ArticleController extends AbstractController
{
    #[Route(name: 'app_article_index', methods: ['GET'])]
    public function index(ArticleRepository $articleRepository, Request $request, EntityManagerInterface $em): Response
    {
        /*$user = new User();
        $user->setFirstname("Olivier");
        $user->setLastname("Rukabo");
        $user->setEmail("moiserukabo@gmail.com");
        $user->setUsername("Olic");
        $user->setPassword("123456");
        $user->setRoles(['ROLE_ADMIN', 'ROLE_USER']);
        
        $em->persist($user);
        $em->flush();*/
        $page = $request->query->getInt('page', 1); // Récupère le numéro de page depuis l'URL
        
        $articles = $articleRepository->findAllPaginated($page);
        return $this->render('admin/article/index.html.twig', [
            'articles' => $articles,
        ]);
    }

    #[Route('/new', name: 'app_article_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $article = new Article();
        $form = $this->createForm(ArticleForm::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $article->setCreatedAt(new DateTimeImmutable());
            $article->setPublishedAt(new DateTimeImmutable());
            $article->setSlug($this->generateSlug($article->getTitle()));
            $article->setAuthor($this->getUser());
            $entityManager->persist($article);
            $entityManager->flush();

            return $this->redirectToRoute('app_article_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/article/new.html.twig', [
            'article' => $article,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_article_show', methods: ['GET'])]
    public function show(Article $article): Response
    {
        return $this->render('admin/article/show.html.twig', [
            'article' => $article,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_article_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Article $article, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ArticleForm::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $article->setUpdatedAt(new DateTimeImmutable());
            
            if ($article->getIsPublished() && !$article->getPublishedAt()) {
                $article->setPublishedAt(new DateTimeImmutable());
            }
            $article->setSlug($this->generateSlug($article->getTitle()));
            $article->setAuthor($this->getUser());
            $entityManager->flush();

            return $this->redirectToRoute('app_article_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/article/edit.html.twig', [
            'article' => $article,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_article_delete', methods: ['POST'])]
    public function delete(Request $request, Article $article, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$article->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($article);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_article_index', [], Response::HTTP_SEE_OTHER);
    }

    function generateSlug(string $string): string
    {
    // Convertir en minuscules
    $string = strtolower($string);

    // Remplacer les caractères accentués par leur version non accentuée
    $string = iconv('UTF-8', 'ASCII//TRANSLIT', $string);

    // Supprimer les caractères spéciaux
    $string = preg_replace('/[^a-z0-9-]/', '-', $string);

    // Supprimer les tirets en trop
    $string = preg_replace('/-+/', '-', $string);

    // Enlever les tirets en début et fin de chaîne
    $string = trim($string, '-');

    return $string;
}
}
