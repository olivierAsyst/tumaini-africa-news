<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact')]
    public function index(): Response
    {
        $contactInfo = [
            'email' => 'mwanadamutumaini@gmail.com',
            'phone' => '+243 97 48 21 55',
            'address' => 'Q. Mulongwe Uvira, RDC',
            'social' => [
                'twitter' => 'https://twitter.com/dev_community',
                'facebook' => 'https://github.com/dev-community',
                'linkedin' => 'https://linkedin.com/company/dev-community'
            ],
            'hours' => [
                'Lundi-Vendredi' => '9h00 - 18h00',
                'Samedi' => '10h00 - 15h00',
                'Dimanche' => 'Fermé'
            ]
        ];

        return $this->render('contact/index.html.twig', [
            'contactInfo' => $contactInfo,
        ]);
    }
}
