<?php

namespace App\Repository;

use App\Entity\Article;
use App\Entity\Category;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @extends ServiceEntityRepository<Article>
 */
class ArticleRepository extends ServiceEntityRepository
{
    private $paginator;
    public function __construct(ManagerRegistry $registry, PaginatorInterface $paginator)
    {
        parent::__construct($registry, Article::class);
        $this->paginator = $paginator;
    }

    /**
     * 
     * @param int $page
     * @param int $limit
     */
    public function findAllPaginated(int $page = 1, int $limit = 20)
    {
        $query = $this->createQueryBuilder('a')
            ->orderBy('a.createdAt', 'DESC') // Exemple de tri par date de création
            ->getQuery();
            
        return $this->paginator->paginate(
            $query,    // Requête Doctrine
            $page,      // Numéro de page
            $limit      // Limite par page
        );
    }

    /**
     * Récupère les 3 derniers articles urgents
     * 
     * @return Article[]
     */
    public function findThreeLatestUrgent(): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.isUrgent = :isUrgent')
            ->setParameter('isUrgent', true)
            ->orderBy('a.publishedAt', 'DESC')
            ->setMaxResults(3)
            ->getQuery()
            ->getResult();
    }

    public function findOneBySlug(string $slug): ?Article
    {
    return $this->createQueryBuilder('a')
        ->where('a.slug = :slug')
        ->setParameter('slug', $slug)
        ->getQuery()
        ->getOneOrNullResult();
    }

    public function findRelatedArticles(int $currentArticleId, ?Category $category = null, int $limit = 3): array
    {
        if ($category !== null) {
        $categoryArticles = $this->createQueryBuilder('a')
            ->where('a.id != :currentId')
            ->andWhere('a.isPublished = true')
            ->andWhere('a.category = :category')
            ->setParameter('currentId', $currentArticleId)
            ->setParameter('category', $category)
            ->orderBy('a.publishedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

            if (count($categoryArticles) >= $limit) {
                return $categoryArticles;
            }
        }
        $qb = $this->createQueryBuilder('a')
        ->where('a.id != :currentId')
        ->andWhere('a.isPublished = true')
        ->setParameter('currentId', $currentArticleId)
        ->orderBy('a.publishedAt', 'DESC')
        ->setMaxResults($limit);
        
        if ($category !== null) {
            $qb->andWhere('a.category != :category OR a.category IS NULL')
                ->setParameter('category', $category);
        }

    $otherArticles = $qb->getQuery()->getResult();

    // Fusionner les résultats si on avait déjà des articles de la même catégorie
    return $category !== null && !empty($categoryArticles) 
        ? array_merge($categoryArticles, $otherArticles)
        : $otherArticles;
    }

    public function findThreeLatestNonUrgentArticles(): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.isPublished = true')
            ->andWhere('a.isUrgent = false OR a.isUrgent IS NULL')
            ->orderBy('a.publishedAt', 'DESC')
            ->setMaxResults(3)
            ->getQuery()
            ->getResult()
            ;
    }

    public function findArticlesByCategorySlug(string $slug, int $limit = null): array
    {
        return $this->createQueryBuilder('a')
            ->join('a.category', 'c')
            ->where('c.slug = :slug')
            ->andWhere('a.isPublished = true')
            ->setParameter('slug', $slug)
            ->orderBy('a.publishedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

public function getPaginatedArticles(string $slug, PaginatorInterface $paginator, Request $request, int $limit = 5): PaginationInterface
{
    $query = $this->createQueryBuilder('a')
        ->join('a.category', 'c')
        ->where('c.slug = :slug')
        ->andWhere('a.isPublished = true')
        ->setParameter('slug', $slug)
        ->orderBy('a.publishedAt', 'DESC')
        ->getQuery();

    return $paginator->paginate(
        $query,
        $request->query->getInt('page', 1),
        $limit
    );
}



public function findMostViewedArticles(int $limit = 5): array
{
    $qb = $this->createQueryBuilder('a')
        ->where('a.isPublished = true')
        ->orderBy('a.viewCount', 'DESC')
        ->setMaxResults($limit);

    $dateLimit = new \DateTime();
    $dateLimit->modify('-48 hours');

    // Clone le QueryBuilder pour garder les mêmes conditions de base
    $recentQb = clone $qb;
    $recentArticles = $recentQb
        ->andWhere('a.publishedAt >= :dateLimit')
        ->setParameter('dateLimit', $dateLimit)
        ->getQuery()
        ->getResult();

    return count($recentArticles) > 0 ? $recentArticles : $qb->getQuery()->getResult();
}
public function findTrendingArticles(int $limit = 10): array
{
    return $this->createQueryBuilder('a')
        ->where('a.isPublished = true')
        ->andWhere('a.publishedAt IS NOT NULL')
        ->orderBy('CASE WHEN a.publishedAt >= :date THEN a.viewCount ELSE 0 END', 'DESC')
        ->addOrderBy('a.publishedAt', 'DESC')
        ->setParameter('date', new DateTime('-1 week'))
        ->setMaxResults($limit)
        ->getQuery()
        ->getResult();
}

//    /**
//     * @return Article[] Returns an array of Article objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Article
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
