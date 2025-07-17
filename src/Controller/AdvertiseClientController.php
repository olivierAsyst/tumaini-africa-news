<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AdvertiseClientController extends AbstractController
{
    #[Route('/advertise', name: 'app_advertise_client')]
    public function index(): Response
    {
        return $this->render('advertise_client/index.html.twig', [
            'controller_name' => 'AdvertiseClientController',
        ]);
    }
}
