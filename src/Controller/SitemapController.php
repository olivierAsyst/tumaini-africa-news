<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class SitemapController extends AbstractController
{
    #[Route('/sitemap.xml', name: 'app_sitemap', defaults: ['_format' => 'xml'])]
    public function index(
        UrlGeneratorInterface $urlGenerator,
        ArticleRepository $articleRepo,
        CategoryRepository $categoryRepo
    ): Response
    {
        $urls = [];

        // 1. Accueil (priorité max, changé quotidiennement)
        $urls[] = [
            'loc' => $urlGenerator->generate('app_home', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'lastmod' => (new \DateTime())->format('Y-m-d'),
            'changefreq' => 'daily',
            'priority' => '1.0'
        ];

        // 2. Articles (dynamiques)
        $articles = $articleRepo->findAll();

        foreach ($articles as $article) {
            $urls[] = [
                'loc' => $urlGenerator->generate('app_single_article_public', ['slug' => $article->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL),
                'lastmod' => $article->getUpdatedAt()->format('Y-m-d'),
                'changefreq' => 'weekly',
                'priority' => '0.9'
            ];
        }

        // 3. Catégories (dynamiques)
        $categories = $categoryRepo->findAll();
        foreach ($categories as $category) {
            $urls[] = [
                'loc' => $urlGenerator->generate('app_category_public', ['slug' => $category->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL),
                'lastmod' => $category->getUpdatedAt() ? $category->getUpdatedAt()->format('Y-m-d') : (new \DateTime())->format('Y-m-d'),
                'changefreq' => 'weekly',
                'priority' => '0.7'
            ];
        }

        // 4. Pages statiques
        $staticPages = [
            ['route' => 'app_podcast_index', 'changefreq' => 'weekly', 'priority' => '0.8'],
            ['route' => 'app_contact', 'changefreq' => 'monthly', 'priority' => '0.5'],
            ['route' => 'app_about', 'changefreq' => 'monthly', 'priority' => '0.5'],
        ];
        
        foreach ($staticPages as $page) {
            $urls[] = [
                'loc' => $urlGenerator->generate($page['route'], [], UrlGeneratorInterface::ABSOLUTE_URL),
                'lastmod' => (new \DateTime())->format('Y-m-d'),
                'changefreq' => $page['changefreq'],
                'priority' => $page['priority']
            ];
        }

        // 5. Génération XML
        $response = new Response(
            $this->renderView('sitemap/sitemap.xml.twig', ['urls' => $urls]),
            200,
            ['Content-Type' => 'application/xml']
        );

        return $response;
    }
}
