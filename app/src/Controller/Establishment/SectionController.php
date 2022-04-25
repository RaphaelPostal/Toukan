<?php

namespace App\Controller\Establishment;

use App\Entity\Card;
use App\Entity\Product;
use App\Entity\Section;
use App\Form\ProductType;
use App\Form\SectionType;
use App\Repository\ProductRepository;
use App\Repository\SectionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\UX\Turbo\TurboBundle;

#[Route('/establishment/card/{card}/section')]
class SectionController extends AbstractController
{
    #[Route('/create', name: 'app_section_create', methods: ['GET', 'POST'])]
    public function new(Request $request, SectionRepository $sectionRepository, Card $card): Response
    {
        $section = new Section();
        $section->setCard($card);
        $form = $this->createForm(SectionType::class, $section, [
            'action' => $this->generateUrl('app_section_create', ['card' => $card->getId()]),
            'save-label' => 'Ajouter',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $sectionRepository->add($section);
            $this->addFlash('success', 'Section ajoutée !');

            if (TurboBundle::STREAM_FORMAT === $request->getPreferredFormat()) {
                // If the request comes from Turbo, set the content type as text/vnd.turbo-stream.html and only send the HTML to update
                $request->setRequestFormat(TurboBundle::STREAM_FORMAT);
                return $this->render('establishment/stream/card.stream.html.twig', ['card' => $card]);
            }
            return $this->redirectToRoute('establishment_card_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('establishment/section/new.html.twig', [
            'section' => $section,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_section_show', methods: ['GET'])]
    public function show(Section $section): Response
    {
        return $this->render('section/show.html.twig', [
            'section' => $section,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_section_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Card $card, Section $section, SectionRepository $sectionRepository): Response
    {
        $form = $this->createForm(SectionType::class, $section, [
            'action' => $this->generateUrl('app_section_edit', ['card' => $card->getId(), 'id' => $section->getId()]),
            'save-label' => 'Enregistrer',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $sectionRepository->add($section);
            $this->addFlash('success', 'Section modifiée !');
            if (TurboBundle::STREAM_FORMAT === $request->getPreferredFormat()) {
                // If the request comes from Turbo, set the content type as text/vnd.turbo-stream.html and only send the HTML to update
                $request->setRequestFormat(TurboBundle::STREAM_FORMAT);
                return $this->render('establishment/stream/card.stream.html.twig', ['card' => $card, 'section' => $section]);
            }
            return $this->redirectToRoute('establishment_card_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('establishment/section/edit.html.twig', [
            'section' => $section,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_section_delete', methods: ['POST'])]
    public function delete(Request $request, Card $card, Section $section, SectionRepository $sectionRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$section->getId(), $request->request->get('_token'))) {
            $sectionRepository->remove($section);
            if (TurboBundle::STREAM_FORMAT === $request->getPreferredFormat()) {
                // If the request comes from Turbo, set the content type as text/vnd.turbo-stream.html and only send the HTML to update
                $request->setRequestFormat(TurboBundle::STREAM_FORMAT);
                return $this->render('establishment/stream/card.stream.html.twig', ['card' => $card]);
            }
        }

        return $this->redirectToRoute('establishment_card_index', [], Response::HTTP_SEE_OTHER);

    }

    #[Route('/{id}/product/new', name: 'app_section_product_new', methods: ['GET', 'POST'])]
    public function newProduct(Request $request, Card $card, Section $section, ProductRepository $productRepository): Response
    {
        $product = new Product();
        $product->setSection($section);
        $product->setCard($card);
        $form = $this->createForm(ProductType::class, $product, [
            'action' => $this->generateUrl('app_section_product_new', ['card' => $card->getId(), 'id' => $section->getId()]),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $productRepository->add($product);
            $this->addFlash('success', 'Produit ajouté !');

            if (TurboBundle::STREAM_FORMAT === $request->getPreferredFormat()) {
                // If the request comes from Turbo, set the content type as text/vnd.turbo-stream.html and only send the HTML to update
                $request->setRequestFormat(TurboBundle::STREAM_FORMAT);
                return $this->render('establishment/stream/card.stream.html.twig', ['card' => $card, 'section' => $section]);
            }
            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('product/new.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }
}
