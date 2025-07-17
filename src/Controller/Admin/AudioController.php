<?php

namespace App\Controller\Admin;

use App\Entity\Audio;
use App\Form\AudioForm;
use App\Repository\AudioRepository;
use App\Service\FormErrorLogger;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use getID3;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/audio')]
final class AudioController extends AbstractController
{
    #[Route(name: 'app_audio_index', methods: ['GET'])]
    public function index(AudioRepository $audioRepository, Request $request): Response
    {

        $page = $request->query->getInt('page', 1); 
        
        $audio = $audioRepository->findAllPaginated($page);
        return $this->render('admin/audio/index.html.twig', [
            'audios' => $audio
        ]);
    }

    #[Route('/new', name: 'app_audio_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, FormErrorLogger $formErrorLogger): Response
    {
        $audio = new Audio();
        $form = $this->createForm(AudioForm::class, $audio);
        $form->handleRequest($request);
        $audioFile = $audio->getAudioFile();
        if ($form->isSubmitted() && $form->isValid()) {;
            if ($audioFile instanceof UploadedFile) {
            $getID3 = new getID3();
            $fileInfo = $getID3->analyze($audioFile->getPathname());
            
            $duration = $fileInfo['playtime_seconds'] ?? null;
            $duration_min = $duration = $duration / 60;
            if ($duration !== null) {
                $audio->setDuration((int)$duration_min);
            }
        }
            $audio->setCreatedAt(new DateTimeImmutable());
            $audio->setPublishedAt(new DateTimeImmutable());
            $audio->setSlug($this->generateSlug($audio->getTitle()));
            $audio->setAuthor($this->getUser());
            
            $entityManager->persist($audio);
            $entityManager->flush();

            return $this->redirectToRoute('app_audio_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/audio/new.html.twig', [
            'audio' => $audio,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_audio_show', methods: ['GET'])]
    public function show(Audio $audio): Response
    {
        return $this->render('admin/audio/show.html.twig', [
            'audio' => $audio,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_audio_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Audio $audio, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AudioForm::class, $audio);
        $form->handleRequest($request);
        $audioFile = $audio->getAudioFile();
        if ($form->isSubmitted() && $form->isValid()) {
            if ($audioFile instanceof UploadedFile) {
            $getID3 = new getID3();
            $fileInfo = $getID3->analyze($audioFile->getPathname());
            
            $duration = $fileInfo['playtime_seconds'] ?? null;
            $duration_min = $duration = $duration / 60;
            if ($duration !== null) {
                $audio->setDuration((int)$duration_min);
            }
        }
            $audio->setUpdatedAt(new DateTimeImmutable());
            $audio->setSlug($this->generateSlug($audio->getTitle()));
            $audio->setAuthor($this->getUser());
            $entityManager->flush();

            return $this->redirectToRoute('app_audio_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/audio/edit.html.twig', [
            'audio' => $audio,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_audio_delete', methods: ['POST'])]
    public function delete(Request $request, Audio $audio, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$audio->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($audio);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_audio_index', [], Response::HTTP_SEE_OTHER);
    }

    function generateSlug(string $string): string
    {
    // Convertir en minuscules
    $string = strtolower($string);

    // Remplacer les caractères accentués par leur version non accentuée
    $string = iconv('UTF-8', 'ASCII//TRANSLIT', $string);

    // Supprimer les caractères spéciaux
    $string = preg_replace('/[^a-z0-9-]/', '-', $string);

    // Supprimer les tirets en trop
    $string = preg_replace('/-+/', '-', $string);

    // Enlever les tirets en début et fin de chaîne
    $string = trim($string, '-');

    return $string;
}
}
