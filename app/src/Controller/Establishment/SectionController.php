<?php

namespace App\Controller\Establishment;

use App\Entity\Product;
use App\Entity\Section;
use App\Form\ProductType;
use App\Form\SectionType;
use App\Repository\ProductRepository;
use App\Repository\SectionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\Turbo\TurboBundle;

#[Route('/establishment/card/section')]
class SectionController extends AbstractController
{
    public function __construct(
        private readonly TranslatorInterface $translator,
    )
    {}

    #[Route('/create', name: 'app_section_create', methods: ['GET', 'POST'])]
    public function new(Request $request, SectionRepository $sectionRepository): Response
    {
        $card = $this->getUser()->getEstablishment()->getCard();

        $section = new Section();
        $card->addSection($section);
        $form = $this->createForm(SectionType::class, $section, [
            'action' => $this->generateUrl('app_section_create', ['card' => $card->getId()]),
            'save-label' => $this->translator->trans('add'),
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

    #[Route('/{id}/edit', name: 'app_section_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Section $section, SectionRepository $sectionRepository): Response
    {
        $card = $this->getUser()->getEstablishment()->getCard();

        $form = $this->createForm(SectionType::class, $section, [
            'action' => $this->generateUrl('app_section_edit', ['card' => $card->getId(), 'id' => $section->getId()]),
            'save-label' => $this->translator->trans('save'),
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
    public function delete(Request $request, Section $section, SectionRepository $sectionRepository): Response
    {
        $card = $this->getUser()->getEstablishment()->getCard();

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

    #[Route('/{id}/product/create', name: 'app_section_product_create', methods: ['GET', 'POST'])]
    public function newProductInSection(Request $request, Section $section, ProductRepository $productRepository, SluggerInterface $slugger): Response
    {
        $card = $this->getUser()->getEstablishment()->getCard();

        $product = new Product();
        $section->addProduct($product);
        $form = $this->createForm(ProductType::class, $product, [
            'action' => $this->generateUrl('app_section_product_create', ['card' => $card->getId(), 'id' => $section->getId()]),
            'save-label' => $this->translator->trans('add')
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $imageFile = $form->get('image')->getData();

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $imageFile->move(
                        $this->getParameter('products_directory') . '/' . $card->getEstablishment()->getId(),
                        $newFilename
                    );
                } catch (FileException) {
                    dump('impossible de déplacer le fichier');
                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $product->setImage($newFilename);
            }

            $productRepository->add($product);

            $this->addFlash('success', 'Produit ajouté !');

            if (TurboBundle::STREAM_FORMAT === $request->getPreferredFormat()) {
                // If the request comes from Turbo, set the content type as text/vnd.turbo-stream.html and only send the HTML to update
                $request->setRequestFormat(TurboBundle::STREAM_FORMAT);
                return $this->render('establishment/stream/card.stream.html.twig', ['card' => $card, 'section' => $section]);
            }
            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('establishment/product/new.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/product/{product}/edit', name: 'app_section_product_edit', methods: ['GET', 'POST'])]
    public function editProduct(Request $request, Product $product, ProductRepository $productRepository, SluggerInterface $slugger): Response
    {
        $card = $this->getUser()->getEstablishment()->getCard();

        $form = $this->createForm(ProductType::class, $product, [
            'action' => $this->generateUrl('app_section_product_edit', ['product' => $product->getId()]),
            'save-label' => 'Enregistrer',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $imageFile = $form->get('image')->getData();

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();
                dump($this->getParameter('products_directory'));

                // Move the file to the directory where brochures are stored
                try {
                    $imageFile->move(
                        $this->getParameter('products_directory') . '/' . $card->getEstablishment()->getId(),
                        $newFilename
                    );
                } catch (FileException) {
                    dump('impossible de déplacer le fichier');
                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $product->setImage($newFilename);
            }

            $productRepository->add($product);

            $this->addFlash('success', 'Produit modifié !');

            if (TurboBundle::STREAM_FORMAT === $request->getPreferredFormat()) {
                // If the request comes from Turbo, set the content type as text/vnd.turbo-stream.html and only send the HTML to update
                $request->setRequestFormat(TurboBundle::STREAM_FORMAT);
                return $this->render('establishment/stream/card.stream.html.twig', ['card' => $card, 'product' => $product]);
            }
            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('establishment/product/edit.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/product/{id}', name: 'app_product_delete', methods: ['POST'])]
    public function deleteProduct(Request $request, Product $product, ProductRepository $productRepository): Response
    {
        $card = $this->getUser()->getEstablishment()->getCard();

        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->request->get('_token'))) {
            $productRepository->remove($product);
            if (TurboBundle::STREAM_FORMAT === $request->getPreferredFormat()) {
                // If the request comes from Turbo, set the content type as text/vnd.turbo-stream.html and only send the HTML to update
                $request->setRequestFormat(TurboBundle::STREAM_FORMAT);
                return $this->render('establishment/stream/card.stream.html.twig', ['card' => $card]);
            }
        }

        return $this->redirectToRoute('establishment_card_index', [], Response::HTTP_SEE_OTHER);
    }
}
