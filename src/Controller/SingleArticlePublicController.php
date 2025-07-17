<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use App\Service\ArticleViewTracker;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SingleArticlePublicController extends AbstractController
{
    #[Route('/article/{slug}', name: 'app_single_article_public', requirements: ['slug' => '[a-z0-9\-]+'])]
    public function index(String $slug, ArticleRepository $articleRepository, ArticleViewTracker $tracker): Response
    {
        $article = $articleRepository->findOneBySlug($slug);
        $tracker->track($article);
        
        $relatedArticles = $articleRepository->findRelatedArticles(
            $article->getId(),
            $article->getCategory(),
            3
        );

        if(!$article){
            throw $this->createNotFoundException("L'article demandé n'existe pas");
        }

        $response = $this->render('single_article_public/index.html.twig', [
            'article' => $article,
            'relatedArticles' => $relatedArticles,
            'controller_name' => 'SingleArticlePublicController'
        ]);
        $response->setPublic();
        $response->setMaxAge(3600);

        return $response;
        
    }
}
