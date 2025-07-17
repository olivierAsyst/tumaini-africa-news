<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ClientUserArticleController extends AbstractController
{
    #[Route('/user/article/{page}', name: 'app_client_user_article', requirements: ['page' => '\d+'], defaults: ['page' => 1])]
    public function index(int $page = 1): Response
    {
         return $this->render('client_user_article/index.html.twig', [
            'currentPage' => $page,
        ]);
    }

}
