<?php

namespace App\Controller\Client;

use App\Entity\Establishment;
use App\Entity\Order;
use App\Entity\ProductOrder;
use App\Entity\Table;
use App\Form\ProductOrderEdit;
use App\Repository\OrderRepository;
use App\Repository\ProductOrderRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use JetBrains\PhpStorm\NoReturn;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\UX\Turbo\TurboBundle;

#[Route('/client/establishment/{establishmentId}/table/{tableId}/order/{orderId}')]

class OrderController extends AbstractController
{
    #[Route('/', name: 'client_order_basket')]
    public function showBasket(OrderRepository $orderRepository,
                               $establishmentId,
                               $tableId,
                               $orderId): Response
    {
        $order = $orderRepository->findOneById($orderId);

        return $this->render('client/order/show_basket.html.twig', [
            'controller_name' => 'OrderController',
            'establishmentId' => $establishmentId,
            'tableId' => $tableId,
            'order' => $order,
            'orderId' => $orderId,
            'total' => 0
        ]);
    }

    #[Route('/product/{productOrderId}/plus', name: 'client_order_basket_product_plus')]
    public function addProductQuantity(Request $request,
                                       ProductOrderRepository $productOrderRepository,
                                       EntityManagerInterface $entityManager,
                                       $productOrderId, $orderId, $tableId, $establishmentId): Response
    {
        $productOrder = $productOrderRepository->find($productOrderId);
        $productOrder->setQuantity($productOrder->getQuantity() + 1);
        $entityManager->persist($productOrder);
        $entityManager->flush();

        $request->setRequestFormat(TurboBundle::STREAM_FORMAT);
        return $this->render('client/order/stream/product.stream.html.twig', ['productOrder' => $productOrder, 'orderId' => $orderId, 'order' => $productOrder->getOrderEntity(), 'tableId' => $tableId, 'establishmentId' => $establishmentId]);

    }

    #[Route('/product/{productOrderId}/delete', name: 'client_order_basket_product_delete')]
    public function deleteProduct(Request $request,
                                       ProductOrderRepository $productOrderRepository,
                                       EntityManagerInterface $entityManager,
        $productOrderId, $orderId, $tableId, $establishmentId): Response
    {
        $productOrder = $productOrderRepository->find($productOrderId);
        $productOrderRepository->remove($productOrder);

        $request->setRequestFormat(TurboBundle::STREAM_FORMAT);
        return $this->render('client/order/stream/product.stream.html.twig', ['productOrder' => $productOrder, 'orderId' => $orderId, 'order' => $productOrder->getOrderEntity(), 'tableId' => $tableId, 'establishmentId' => $establishmentId]);

    }

    #[Route('/product/{productOrderId}/minus', name: 'client_order_basket_product_minus')]
    public function removeProductQuantity(Request $request,
                                          ProductOrderRepository $productOrderRepository,
                                          EntityManagerInterface $entityManager,
        $productOrderId, $orderId, $tableId, $establishmentId): Response
    {
        $productOrder = $productOrderRepository->find($productOrderId);
        $productOrder->setQuantity($productOrder->getQuantity() - 1);
        $entityManager->persist($productOrder);
        $entityManager->flush();

        $request->setRequestFormat(TurboBundle::STREAM_FORMAT);
        return $this->render('client/order/stream/product.stream.html.twig', ['productOrder' => $productOrder, 'orderId' => $orderId, 'order' => $productOrder->getOrderEntity(), 'tableId' => $tableId, 'establishmentId' => $establishmentId]);

    }

}
