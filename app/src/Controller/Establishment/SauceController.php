<?php

namespace App\Controller\Establishment;

use App\Entity\Card;
use App\Entity\Sauce;
use App\Entity\Section;
use App\Form\SauceType;
use App\Repository\SauceRepository;
use App\Repository\SectionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\UX\Turbo\TurboBundle;

#[Route('establishment/card/{card}/sauce')]
class SauceController extends AbstractController
{


    #[Route('/create', name: 'app_sauce_create', methods: ['GET', 'POST'])]
    public function new(Request $request, Card $card, SauceRepository $sauceRepository, SectionRepository $sectionRepository, EntityManagerInterface$entityManager): Response
    {
        $sauce = new Sauce();
        $form = $this->createForm(SauceType::class, $sauce, [
            'action' => $this->generateUrl('app_sauce_create', ['card' => $card->getId()]),
            'save-label' => 'Ajouter',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //if a section named "sauce" exists, we add the sauce to it
            $section_sauce = $sectionRepository->findOneBy(['title' => 'Sauces']);
            if($section_sauce){
                $section_sauce->addSauce($sauce);
            } else {
                //else, we create a new section named "sauce" and add the sauce to it
                $section_sauce = new Section();
                $section_sauce->setTitle('Sauces');
                $section_sauce->addSauce($sauce);
                $section_sauce->setCard($card);
                $entityManager->persist($section_sauce);

            };
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

    #[Route('/{id}/edit', name: 'app_sauce_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Card $card, Sauce $sauce, SauceRepository $sauceRepository): Response
    {
        $form = $this->createForm(SauceType::class, $sauce, [
            'action' => $this->generateUrl('app_sauce_edit', ['card' => $card->getId(), 'id' => $sauce->getId()]),
            'save-label' => 'Enregistrer',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $sauceRepository->add($sauce);
            if (TurboBundle::STREAM_FORMAT === $request->getPreferredFormat()) {
                // If the request comes from Turbo, set the content type as text/vnd.turbo-stream.html and only send the HTML to update
                $request->setRequestFormat(TurboBundle::STREAM_FORMAT);
                $this->addFlash('success', 'Sauce modifiée !');
                return $this->render('establishment/stream/card.stream.html.twig', ['card' => $card, 'sauce' => $sauce]);
            }
            return $this->redirectToRoute('establishment_card_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('sauce/edit.html.twig', [
            'sauce' => $sauce,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_sauce_delete', methods: ['POST'])]
    public function delete(Request $request, Card $card, Sauce $sauce, SauceRepository $sauceRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$sauce->getId(), $request->request->get('_token'))) {
            $sauceRepository->remove($sauce);

            if (TurboBundle::STREAM_FORMAT === $request->getPreferredFormat()) {
                // If the request comes from Turbo, set the content type as text/vnd.turbo-stream.html and only send the HTML to update
                $request->setRequestFormat(TurboBundle::STREAM_FORMAT);
                return $this->render('establishment/stream/card.stream.html.twig', ['card' => $card, 'sauce' => $sauce]);
            }
        }

        return $this->redirectToRoute('establishment_card_index', [], Response::HTTP_SEE_OTHER);
    }
}
