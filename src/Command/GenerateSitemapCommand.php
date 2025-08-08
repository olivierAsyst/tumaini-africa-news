<?php

namespace App\Command;

use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AsCommand(
    name: 'app:generate-sitemap',
    description: 'Generate static sitemap.xml file'
)]
class GenerateSitemapCommand extends Command
{
    private $urlGenerator;
    private $articleRepo;
    private $categoryRepo;

    public function __construct(
        UrlGeneratorInterface $urlGenerator,
        ArticleRepository $articleRepo,
        CategoryRepository $categoryRepo
    )
    {
        $this->urlGenerator = $urlGenerator;
        $this->articleRepo = $articleRepo;
        $this->categoryRepo = $categoryRepo;

        parent::__construct();
    }
    protected function configure(): void
    {
        // $this
        //     ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
        //     ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        // ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $urls = [];
        
        // Ajoutez ici la même logique que dans votre SitemapController
        $urls[] = [
            'loc' => 'https://tumainiafricanews.info' . $this->urlGenerator->generate('app_home'),
            'lastmod' => date('Y-m-d'),
            'priority' => '1.0'
        ];

        // Articles
        foreach ($this->articleRepo->findAll() as $article) {
            $updatedAt = $article->getUpdatedAt() ? $article->getUpdatedAt()->format('Y-m-d') : date('Y-m-d');
            
            $urls[] = [
                'loc' => 'https://tumainiafricanews.info' . $this->urlGenerator->generate('app_single_article_public', 
                    ['slug' => $article->getSlug()]),
                'lastmod' => $updatedAt,
                'priority' => '0.9'
            ];
        }

                // 3. Catégories (dynamiques)
        foreach ($this->categoryRepo->findAll() as $category) {
            $updatedAt = $category->getUpdatedAt() ? $category->getUpdatedAt()->format('Y-m-d') : date('Y-m-d');
            $urls[] = [
                'loc' => 'https://tumainiafricanews.info' . $this->urlGenerator->generate('app_category_public', ['slug' => $category->getSlug()]),
                'lastmod' => $updatedAt, //$category->getUpdatedAt() ? $category->getUpdatedAt()->format('Y-m-d') : (new \DateTime())->format('Y-m-d'),
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
                'loc' => 'https://tumainiafricanews.info' . $this->urlGenerator->generate($page['route']),
                'lastmod' => (new \DateTime())->format('Y-m-d'),
                'changefreq' => $page['changefreq'],
                'priority' => $page['priority']
            ];
        }

        // Génération du XML
        $xml = $this->renderSitemapXml($urls);
        
        // Écriture dans public/sitemap.xml
        file_put_contents('public/sitemap.xml', $xml);
        
        $output->writeln('Sitemap generated successfully!');
        return Command::SUCCESS;
    }

        private function renderSitemapXml(array $urls): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
        <urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        foreach ($urls as $url) {
            $xml .= '
            <url>
                <loc>'.htmlspecialchars($url['loc']).'</loc>
                <lastmod>'.$url['lastmod'].'</lastmod>
                <priority>'.$url['priority'].'</priority>
            </url>';
        }

        $xml .= '
        </urlset>';

        return $xml;
    }
}
