<?php

namespace App\Controller\Admin;

use App\Repository\ArticleRepository;
use App\Repository\AudioRepository;
use App\Repository\CategoryRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function index(
        CategoryRepository $categoryRepository,
        ArticleRepository $articleRepository,
        AudioRepository $audioRepository,
        UserRepository $userRepository
    ): Response
    {
        //Voir si l'utilisateur a le role d'y entrer
        // if (!$this->isGranted('ROLE_USER')) {
        //     return $this->redirectToRoute('app_login');
        // }

        // if ($this->isGranted('ROLE_ADMIN')) {
        $categories = $categoryRepository->findAll();
        $articles = $articleRepository->findBy([], ['publishedAt' => 'DESC'], 10);
        $audios = $audioRepository->findBy([], ['publishedAt' => 'DESC'], 5);
        $users = $userRepository->findAll();
        // dd($users);

        $stats = [
            'totalArticles' => count($articleRepository->findAll()),
            'totalUsers' => count($users),
            'totalAudios' => count($audioRepository->findAll()),
        ];
    // }
    //     else{
            // $user = $this->getUser();
            // $articles = $articleRepository->findByUser($user);
            // $audios = [];
            // $users = [];
            
            // $stats = [
            //     'totalArticles' => count($articles),
            // ];
        // }

        return $this->render('admin/dashboard.html.twig', [
            'controller_name' => 'AdminController',
            'categories' => $categories,
            'articles' => $articles,
            'audios' => $audios,
            'users' => $users,
            'stats' => $stats,
        ]);
    }
}
