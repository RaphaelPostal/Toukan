<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/establishment/direct')]
class DirectController extends AbstractController
{
    #[Route('/waiter', name: 'direct_waiter')]
    public function waiterIndex(): Response
    {
        $orders = $this->getUser()->getEstablishment()->getOrders();

        return $this->render('establishment/direct/waiter_index.html.twig', [
            'orders' => $orders,
        ]);
    }

    #[Route('/kitchen', name: 'direct_kitchen')]
    public function kitchenIndex(): Response
    {
        $orders = $this->getUser()->getEstablishment()->getOrders();

        return $this->render('establishment/direct/kitchen_index.html.twig', [
            'orders' => $orders,
        ]);
    }
}
