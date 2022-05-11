<?php

namespace App\Controller\Establishment;

use App\Entity\Card;
use App\Entity\Sauce;
use App\Form\SauceType;
use App\Repository\SauceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\UX\Turbo\TurboBundle;

#[Route('establishment/card/{card}/sauce')]
class SauceController extends AbstractController
{
    #[Route('/', name: 'app_sauce_index', methods: ['GET'])]
    public function index(SauceRepository $sauceRepository, Card $card): Response
    {
        return $this->render('sauce/index.html.twig', [
            'sauces' => $sauceRepository->findAll(),
        ]);
    }

    #[Route('/create', name: 'app_sauce_create', methods: ['GET', 'POST'])]
    public function new(Request $request, Card $card, SauceRepository $sauceRepository): Response
    {
        $sauce = new Sauce();
        $form = $this->createForm(SauceType::class, $sauce, [
            'action' => $this->generateUrl('app_sauce_create', ['card' => $card->getId()]),
            'save-label' => 'Ajouter',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $card->addSauce($sauce);
            $sauceRepository->add($sauce);
            if (TurboBundle::STREAM_FORMAT === $request->getPreferredFormat()) {
                // If the request comes from Turbo, set the content type as text/vnd.turbo-stream.html and only send the HTML to update
                $request->setRequestFormat(TurboBundle::STREAM_FORMAT);
                $this->addFlash('success', 'Sauce ajoutée !');
                return $this->render('establishment/stream/card.stream.html.twig', ['card' => $card]);

            }
            return $this->redirectToRoute('establishment_card_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('sauce/new.html.twig', [
            'sauce' => $sauce,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_sauce_show', methods: ['GET'])]
    public function show(Sauce $sauce): Response
    {
        return $this->render('sauce/show.html.twig', [
            'sauce' => $sauce,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_sauce_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Sauce $sauce, SauceRepository $sauceRepository): Response
    {
        $form = $this->createForm(SauceType::class, $sauce);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $sauceRepository->add($sauce);
            return $this->redirectToRoute('app_sauce_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('sauce/edit.html.twig', [
            'sauce' => $sauce,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_sauce_delete', methods: ['POST'])]
    public function delete(Request $request, Sauce $sauce, SauceRepository $sauceRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$sauce->getId(), $request->request->get('_token'))) {
            $sauceRepository->remove($sauce);
        }

        return $this->redirectToRoute('app_sauce_index', [], Response::HTTP_SEE_OTHER);
    }
}
