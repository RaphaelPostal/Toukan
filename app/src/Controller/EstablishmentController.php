<?php

namespace App\Controller;

use App\Entity\Establishment;
use App\Form\EstablishmentInfoType;
use App\Repository\EstablishmentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/establishment')]
class EstablishmentController extends AbstractController
{
    #[Route('/', name: 'app_establishment_index', methods: ['GET'])]
    public function index(EstablishmentRepository $establishmentRepository): Response
    {
        return $this->render('establishment/index.html.twig', [
            'establishments' => $establishmentRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_establishment_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EstablishmentRepository $establishmentRepository): Response
    {
        $establishment = new Establishment();
        $form = $this->createForm(EstablishmentInfoType::class, $establishment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $establishmentRepository->add($establishment);
            return $this->redirectToRoute('app_establishment_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('establishment/new.html.twig', [
            'establishment' => $establishment,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_establishment_show', methods: ['GET'])]
    public function show(Establishment $establishment): Response
    {
        return $this->render('establishment/show.html.twig', [
            'establishment' => $establishment,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_establishment_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Establishment $establishment, EstablishmentRepository $establishmentRepository): Response
    {
        $form = $this->createForm(EstablishmentInfoType::class, $establishment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $establishmentRepository->add($establishment);
            return $this->redirectToRoute('app_establishment_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('establishment/edit.html.twig', [
            'establishment' => $establishment,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_establishment_delete', methods: ['POST'])]
    public function delete(Request $request, Establishment $establishment, EstablishmentRepository $establishmentRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$establishment->getId(), $request->request->get('_token'))) {
            $establishmentRepository->remove($establishment);
        }

        return $this->redirectToRoute('app_establishment_index', [], Response::HTTP_SEE_OTHER);
    }
}
