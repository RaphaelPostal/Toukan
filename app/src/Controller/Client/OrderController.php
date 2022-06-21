<?php

namespace App\Controller\Client;

use App\Entity\Establishment;
use App\Entity\Order;
use App\Entity\Product;
use App\Entity\ProductOrder;
use App\Entity\Table;
use App\Form\OrderCommentsType;
use App\Repository\EstablishmentRepository;
use App\Repository\OrderRepository;
use App\Repository\ProductOrderRepository;
use App\Repository\ProductRepository;
use App\Repository\SauceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\UX\Turbo\TurboBundle;

#[Route('/client/establishment/{establishment}/table/{table}/order/{order}')]
class OrderController extends AbstractController
{
    #[Route('/', name: 'client_order_basket')]
    public function showBasket(OrderRepository         $orderRepository,
                               EntityManagerInterface  $entityManager,
                               EstablishmentRepository $establishmentRepository,
                               Request $request,
                               Establishment $establishment,
                               Table $table,
                               Order $order): Response
    {
        $form = $this->createForm(OrderCommentsType::class);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $order->setEstablishment($establishment);
            $order->setCustomInfos($form->get('custom_infos')->getData());
            $order->setCreatedAt(new \DateTimeImmutable('now', new \DateTimeZone('Europe/Paris')));
            $order->setStatus(Order::STATUS_IN_PROGRESS);
            $entityManager->persist($order);
            $entityManager->flush();
            return $this->redirectToRoute('client_order_confirm', [
                'establishment' => $establishment,
                'table' => $table,
                'order' => $order
            ]);
        }
        $establishment = $establishmentRepository->find($establishment);

        return $this->render('client/order/show_basket.html.twig', [
            'establishment' => $establishment,
            'table' => $table,
            'order' => $order,
            'total' => 0,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/product/{product}/add/{sauce}/{drink}/{dessert}', name: 'client_order_add_product')]
    public function addProduct(EntityManagerInterface $entityManager,
                                Establishment $establishment,
                                ProductOrderRepository $productOrderRepository,
                                ProductRepository $productRepository,
                                SauceRepository $sauceRepository,
                                Table $table,
                                Order $order,
                                Product $product,
                                $sauce,
                                $drink,
                                $dessert,
                                Request $request): Response
    {
        $sauce = $sauceRepository->find($sauce);
        $drink = $productRepository->find($drink);
        $dessert = $productRepository->find($dessert);
        if($productOrderRepository->isProductAlreadyInBasket($order, $product, $sauce, $drink, $dessert)) {
            $productOrder = $productOrderRepository->findOneBy(['orderEntity' => $order, 'product' => $product, 'sauce' => $sauce, 'drink' => $drink, 'dessert' => $dessert]);
            $productOrder->setQuantity($productOrder->getQuantity() + 1);
        } else {
            $productOrder = new ProductOrder();
            $productOrder->setOrderEntity($order);
            $productOrder->setProduct($product);
            $productOrder->setQuantity(1);
            $productOrder->setSauce($sauce);
            $productOrder->setDrink($drink);
            $productOrder->setDessert($dessert);
        }
        $entityManager->persist($productOrder);
        $entityManager->flush();

        $nbrProducts = 0;
        foreach ($order->getProductOrders() as $product) {
            $nbrProducts += $product->getQuantity();
        }

        return $this->json([
            'nbrProducts' => $nbrProducts
        ]);
    }

    #[Route('/product/{productOrder}/plus', name: 'client_order_basket_product_plus')]
    public function addProductQuantity(Request $request,
                                       EntityManagerInterface $entityManager,
                                       ProductOrder $productOrder,
                                       Order $order,
                                       Table $table,
                                       Establishment $establishment): Response
    {
        $form = $this->createForm(OrderCommentsType::class);
        $form->handleRequest($request);
        $productOrder->setQuantity($productOrder->getQuantity() + 1);
        $entityManager->persist($productOrder);
        $entityManager->flush();

        $request->setRequestFormat(TurboBundle::STREAM_FORMAT);
        return $this->render('client/order/stream/product.stream.html.twig', [
            'productOrder' => $productOrder,
            'order' => $order,
            'table' => $table,
            'establishment' => $establishment,
            'form' => $form->createView()
        ]);
    }

    #[Route('/product/{productOrder}/delete', name: 'client_order_basket_product_delete')]
    public function deleteProduct(Request $request,
                                  ProductOrderRepository $productOrderRepository,
                                  ProductOrder $productOrder,
                                  Order $order,
                                  Table $table,
                                  Establishment $establishment): Response
    {
        $form = $this->createForm(OrderCommentsType::class);
        $form->handleRequest($request);
        $productOrderRepository->remove($productOrder);
        $request->setRequestFormat(TurboBundle::STREAM_FORMAT);
        return $this->render('client/order/stream/product.stream.html.twig', [
            'productOrder' => $productOrder,
            'order' => $order,
            'table' => $table,
            'establishment' => $establishment,
            'form' => $form->createView()
            ]);
    }

    #[Route('/product/{productOrder}/minus', name: 'client_order_basket_product_minus')]
    public function removeProductQuantity(Request $request,
                                       EntityManagerInterface $entityManager,
                                       ProductOrder $productOrder,
                                       Order $order,
                                       Table $table,
                                       Establishment $establishment): Response
    {
        $form = $this->createForm(OrderCommentsType::class);
        $form->handleRequest($request);
        $productOrder->setQuantity($productOrder->getQuantity() - 1);
        $entityManager->persist($productOrder);
        $entityManager->flush();

        $request->setRequestFormat(TurboBundle::STREAM_FORMAT);
        return $this->render('client/order/stream/product.stream.html.twig', [
            'productOrder' => $productOrder,
            'order' => $order,
            'table' => $table,
            'establishment' => $establishment,
            'form' => $form->createView()
        ]);
    }

    #[Route('/confirm', name: 'client_order_confirm')]
    public function confirmOrder(Order           $order,
                                 Table           $table,
                                 Establishment   $establishment,
                                 OrderRepository $orderRepository,
                                 HubInterface    $hub
    ): Response
    {
        $waitingListRank = (is_countable($orderRepository->getPreviousOrders($order)) ? count($orderRepository->getPreviousOrders($order)) : 0) + 1;

        $update = new Update(
            'toukan/order/confirm',
            json_encode(
                [
                    'order' => $order,
                    'table' => $table,
                    'establishment' => $establishment,
                    'waitingListRank' => $waitingListRank
                ]
            )
        );
        $hub->publish($update);

        return $this->render('client/order/confirm.html.twig', [
            'waitingListRank' => $waitingListRank,
            'order' => $order,
            'table' => $table,
            'establishment' => $establishment
        ]);

    }

    #[Route('/confirm/update', name: 'client_order_confirm_update')]
    public function confirmOrderUpdate(Order $order, OrderRepository $orderRepository,)
    {
        $waitingListRank = (is_countable($orderRepository->getPreviousOrders($order)) ? count($orderRepository->getPreviousOrders($order)) : 0) + 1;
        return $this->render('client/order/waiting-list.html.twig', [
            'waitingListRank' => $waitingListRank,
        ]);
    }

}
