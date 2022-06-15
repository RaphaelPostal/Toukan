<?php

namespace App\Controller\Client;

use App\Entity\Establishment;
use App\Entity\Order;
use App\Entity\Table;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/client/establishment')]
class CardController extends AbstractController
{
    #[Route('/{establishment}/card/table/{table}', name: 'app_client_card')]
    public function index(Establishment $establishment,
                          Table $table,
                          OrderRepository $orderRepository,
                          EntityManagerInterface $entityManager): Response
    {
        if($orderRepository->isTableOrdered($table) == false){
            $order = new Order();
            $order->setEstablishmentTable($table);
            $order->setCreatedAt(new \DateTimeImmutable('now', new \DateTimeZone('Europe/Paris')));
            $order->setStatus(Order::STATUS_ORDERING);
            $entityManager->persist($order);
            $entityManager->flush();
        } else {
            $order = $orderRepository->getLastOrderByTable($table);
        }
        $card = $establishment->getCard();

        $nbrProducts = 0;
        foreach ($order->getProductOrders() as $product) {
            $nbrProducts += $product->getQuantity();
        }

        return $this->render('client/card/index.html.twig', [
            'card' => $card,
            'controller_name' => 'CardController',
            'establishment' => $establishment,
            'order' => $order,
            'table' => $table,
            'nbrProducts' => $nbrProducts
        ]);
    }
}
