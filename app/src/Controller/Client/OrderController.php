<?php

namespace App\Controller\Client;

use App\Entity\Order;
use App\Form\OrderCommentsType;
use App\Repository\OrderRepository;
use App\Repository\ProductOrderRepository;
use Doctrine\ORM\EntityManagerInterface;
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
                               EntityManagerInterface $entityManager,
                               Request $request,
                               $establishmentId,
                               $tableId,
                               $orderId): Response
    {
        $order = $orderRepository->findOneById($orderId);
        $form = $this->createForm(OrderCommentsType::class);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $order->setCustomInfos($form->get('custom_infos')->getData());
            $order->setCreatedAt(new \DateTimeImmutable('now', new \DateTimeZone('Europe/Paris')));
            $order->setStatus(Order::STATUS_IN_PROGRESS);
            $entityManager->persist($order);
            $entityManager->flush();
            return $this->redirectToRoute('client_order_confirm', [
                'establishmentId' => $establishmentId,
                'tableId' => $tableId,
                'orderId' => $orderId
            ]);
        }

        return $this->render('client/order/show_basket.html.twig', [
            'establishmentId' => $establishmentId,
            'tableId' => $tableId,
            'order' => $order,
            'orderId' => $orderId,
            'total' => 0,
            'form' => $form->createView()
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

    #[Route('/confirm', name: 'client_order_confirm')]
    public function confirmOrder($orderId, $tableId, $establishmentId, OrderRepository $orderRepository): Response
    {
        $waitingListRank = count($orderRepository->getPreviousOrders($orderRepository->find($orderId))) + 1;
        return $this->render('client/order/confirm.html.twig', [
            'waitingListRank' => $waitingListRank,
            'orderId' => $orderId,
            'tableId' => $tableId,
            'establishmentId' => $establishmentId
        ]);

    }

    #[Route('/confirm/update', name: 'client_order_confirm_update')]
    public function confirmOrderUpdate($orderId, $tableId, $establishmentId, OrderRepository $orderRepository, Request $request)
    {
        $waitingListRank = count($orderRepository->getPreviousOrders($orderRepository->find($orderId))) + 1;
        return $this->render('client/order/waiting-list.html.twig', [
            'waitingListRank' => $waitingListRank,
            'orderId' => $orderId,
            'tableId' => $tableId,
            'establishmentId' => $establishmentId
        ]);
    }

}
