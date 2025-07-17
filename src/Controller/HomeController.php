<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\User;
use App\Repository\AdvertiseRepository;
use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(ArticleRepository $articleRepo, 
        CategoryRepository $categoryRepository,
        AdvertiseRepository $advertiseRepository,
        Request $request): Response
    {
        $last_urgent_articles = $articleRepo->findThreeLatestUrgent();
        $categoriesResult = $categoryRepository->findTopCategoriesWithMostArticles(7);
        $categories = array_map(function($item){
            return $item[0];
        }, $categoriesResult);
        $selectedCategorySlug = $request->query->get('category');
        $selectedCategory = $selectedCategorySlug 
            ? $categoryRepository->findOneBy(['slug' => $selectedCategorySlug])
            : ($categories[0] ?? null);
        
        $categoryArticles = [];
        if($selectedCategory){
            $categoryArticles = $articleRepo->findBy(
                ['category' => $selectedCategory, 'isPublished' => true],
                ['publishedAt' => 'DESC'],
                4
            );
        }

        $mostViewed = $articleRepo->findMostViewedArticles();
        $firstViewed = $mostViewed[0];
        $trending = $articleRepo->findTrendingArticles();
        $last_advertise = $advertiseRepository->findLastThreeWhereIsMiddleFalseOrNull();
        $last_advertise_middle = $advertiseRepository->findLastMiddleAdvertise();
        $nonUrgentLastThree = $articleRepo->findThreeLatestNonUrgentArticles();
        return $this->render('home/index.html.twig', [
            'controller_name' => 'Acceuil !',
            'lastFirst' => $nonUrgentLastThree[0] ?? null,
            'lastSecond' => $nonUrgentLastThree[1] ?? null,
            'lastThird' => $nonUrgentLastThree[2] ?? null,
            'urgents' => $last_urgent_articles,
            'categories' => $categories,
            'selected_category' => $selectedCategory,
            'category_articles' => $categoryArticles,
            'first_viewed' => $firstViewed,
            'most_viewed' => $mostViewed,
            'trending' => $trending,
            'last_advertise' => $last_advertise,
            'advertises_count'=> count($last_advertise),
            'middle_advertise' => $last_advertise_middle
        ]);
    }

}
