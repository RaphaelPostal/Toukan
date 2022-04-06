<?php

namespace App\Controller\Establishment;

use App\Entity\Section;
use App\Form\SectionType;
use App\Repository\CardRepository;
use App\Repository\SectionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/establishment/card')]
class CardController extends AbstractController
{
    /**
     * @param EntityManagerInterface $entityManager
     * @param CardRepository $cardRepository
     * @return Response
     */
    #[Route('', name: 'establishment_card_index')]
    public function index(EntityManagerInterface $entityManager, CardRepository $cardRepository, SectionRepository $sectionRepository, Request $request): Response
    {
        //use when project is more advanced
        // $card = $this->getUser()->getEstablishment()->getCard();

        //find the first card in database
        $card = $cardRepository->find(1);

        $section = new Section();
        $form = $this->createForm(SectionType::class, $section);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $section->setCard($card);
            $entityManager->persist($section);
            $entityManager->flush();
        }

        return $this->render('establishment/card/index.html.twig', [
            'card' => $card,
        ]);
    }
}
