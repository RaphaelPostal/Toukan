<?php

namespace App\Controller\Client;

use App\Repository\OrderRepository;
use App\Repository\ProductOrderRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderController extends AbstractController
{
    #[Route('/client/establishment/{establishmentId}/table/{tableId}/order/{orderId}', name: 'client_order_basket')]
    public function showBasket(ProductOrderRepository $productOrderRepository,
                               OrderRepository $orderRepository,
                               $establishmentId,
                               $tableId,
                               $orderId): Response
    {
        $order = $orderRepository->findOneById($orderId);
        $productOrders = $order->getProductOrders();

        return $this->render('client/order/show_basket.html.twig', [
            'controller_name' => 'OrderController',
            'establishmentId' => $establishmentId,
            'tableId' => $tableId,
            'order' => $order,
            'productOrders' => $productOrders,
        ]);
    }

    //delete one elemnt in productOrder
    #[Route('/client/establishment/{establishmentId}/table/{tableId}/order/{orderId}/product/{productOrderId}/delete', name: 'client_order_remove_product')]
    public function deleteProduct(EntityManagerInterface $em, ProductOrderRepository $productOrderRepository,
                                  $establishmentId,
                                  $tableId,
                                  $orderId,
                                  $productOrderId): Response
    {
        $productOrder = $productOrderRepository->findOneById($productOrderId);
        $em->remove($productOrder);
        $em->flush();
        return $this->redirectToRoute('client_order_basket', [
            'establishmentId' => $establishmentId,
            'tableId' => $tableId,
            'orderId' => $orderId,
        ]);
    }
}
