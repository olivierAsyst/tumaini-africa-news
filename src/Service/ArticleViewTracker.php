<?php

namespace App\Service;

use App\Entity\Article;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class ArticleViewTracker
{
    public function __construct(
        private EntityManagerInterface $em,
        private RequestStack $requestStack
    ){}

    public function track(Article $article)
    {
        $session = $this->requestStack->getSession();
        if(!$session->get('viewed_'.$article->getId(), false)){
            $article->incrementCount();
            $this->em->flush();
            $session->set('viewed_'.$article->getId(), true);
        }
    }
}