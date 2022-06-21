<?php

namespace App\Controller;

use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/establishment/direct')]
class DirectController extends AbstractController
{

    public function __construct(
        private readonly OrderRepository        $orderRepository,
        private readonly EntityManagerInterface $entityManager
    )
    {
    }

    #[Route('/', name: 'direct_index')]
    public function index(): Response
    {
        return $this->render('establishment/direct/index.html.twig');
    }

    #[Route('/waiter', name: 'direct_waiter')]
    public function waiterIndex(): Response
    {
        $orders = $this->getUser()->getEstablishment()->getOrders();

        return $this->render('establishment/direct/waiter_index.html.twig', [
            'orders' => $orders,
        ]);
    }

    #[Route('/waiter/confirm-order/{order}', name: 'direct_waiter_confirm_order')]
    public function waiterConfirmOrder($order): Response
    {
        $order = $this->orderRepository->find($order);

        $order->setStatus($order::STATUS_DELIVERED);

        $this->entityManager->flush();

        return $this->redirectToRoute('direct_waiter');
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
