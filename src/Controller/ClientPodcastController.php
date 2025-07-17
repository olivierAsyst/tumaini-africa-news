<?php

namespace App\Controller;

use App\Repository\AudioRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ClientPodcastController extends AbstractController
{
    #[Route('/podcasts', name: 'app_podcast_index')]
    public function index(AudioRepository $audioRepository, Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $audios = $audioRepository->findAllPaginated($page,4);
        return $this->render('client_podcast/index.html.twig', [
            'audios' => $audios
        ]);
    }


    #[Route('/podcasts/{slug}', name: 'app_podcast_show', requirements: ['slug' => '[a-z0-9\-]+'])]
    public function show(AudioRepository $audioRepository, String $slug): Response
    {

        $audio = $audioRepository->findOneBy(['slug' => $slug]);

        return $this->render('client_podcast/show.html.twig', [
            'audio' => $audio
        ]);
    }

}