<?php

namespace App\Controller\Client;

use App\Entity\Establishment;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/customer/establishment')]
class CardController extends AbstractController
{
    #[Route('/{id}/card', name: 'app_client_card')]
    public function index(Establishment $establishment): Response
    {   
        $card = $establishment->getCard();

        return $this->render('client/card/index.html.twig', [
            'card' => $card,
            'controller_name' => 'CardController',
        ]);
    }
}
