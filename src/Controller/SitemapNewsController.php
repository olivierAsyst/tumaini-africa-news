<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class SitemapNewsController extends AbstractController
{
#[Route('/sitemap-news.xml', name: 'app_sitemap_news', defaults: ['_format' => 'xml'])]
public function news(
    UrlGeneratorInterface $urlGenerator,
    ArticleRepository $articleRepo
): Response
{
    $since = new \DateTime('-48 hours');
    $rows = $articleRepo->createQueryBuilder('a')
        ->select('a.slug, a.title, a.createdAt')
        ->where('a.createdAt >= :since')
        ->setParameter('since', $since)
        ->orderBy('a.createdAt', 'DESC')
        ->getQuery()
        ->getArrayResult();

    $articles = array_map(function($r) use ($urlGenerator) {
        $dt = $r['createdAt'] instanceof \DateTimeInterface ? $r['createdAt'] : new \DateTime($r['createdAt']);
        return [
            'loc' => $urlGenerator->generate('app_single_article_public', ['slug' => $r['slug']], UrlGeneratorInterface::ABSOLUTE_URL),
            'publication_date' => $dt->format('Y-m-d'),
            'title' => $r['title'],
        ];
    }, $rows);

    return new Response(
        $this->renderView('sitemap/sitemap-news.xml.twig', ['articles' => $articles]),
        200,
        ['Content-Type' => 'application/xml']
    );
}
}
