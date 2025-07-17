<?php

namespace App\Controller\Admin;

use App\Entity\Advertise;
use App\Form\AdvertiseForm;
use App\Repository\AdvertiseRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/advertise')]
final class AdvertiseController extends AbstractController
{
    #[Route(name: 'app_advertise_index', methods: ['GET'])]
    public function index(AdvertiseRepository $advertiseRepository): Response
    {
        return $this->render('admin/advertise/index.html.twig', [
            'advertises' => $advertiseRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_advertise_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $advertise = new Advertise();
        $form = $this->createForm(AdvertiseForm::class, $advertise);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $advertise->setCreatedAt(new DateTimeImmutable());
            $entityManager->persist($advertise);
            $entityManager->flush();

            return $this->redirectToRoute('app_advertise_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/advertise/new.html.twig', [
            'advertise' => $advertise,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_advertise_show', methods: ['GET'])]
    public function show(Advertise $advertise): Response
    {
        return $this->render('admin/advertise/show.html.twig', [
            'advertise' => $advertise,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_advertise_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Advertise $advertise, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AdvertiseForm::class, $advertise);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $advertise->setUpdatedAt(new DateTimeImmutable());
            $entityManager->flush();

            return $this->redirectToRoute('app_advertise_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/advertise/edit.html.twig', [
            'advertise' => $advertise,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_advertise_delete', methods: ['POST'])]
    public function delete(Request $request, Advertise $advertise, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$advertise->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($advertise);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_advertise_index', [], Response::HTTP_SEE_OTHER);
    }
}
