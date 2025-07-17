<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CateryPubliController extends AbstractController
{
    #[Route('/category/{slug}', name: 'app_category_public', requirements: ['slug' => '[a-z0-9\-]+'])]
    public function index(String $slug, ArticleRepository $articleRepo, PaginatorInterface $paginator, Request $request): Response
    {
        $latestArticles = $articleRepo->findArticlesByCategorySlug($slug, 5);
        $categoryName = $latestArticles[0];
        // $page = $request->query->getInt('page', 1);
        $otherArticles = $articleRepo->getPaginatedArticles($slug, $paginator, $request);
        
        // dd($otherArticles);
        return $this->render('catery_publi/index.html.twig', [
            'latestArticles' => $latestArticles,
            'category_name' => $categoryName,
            'other_articles' => $otherArticles
        ]);
    }
}
