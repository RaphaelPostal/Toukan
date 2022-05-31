<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

class HomeController extends AbstractController
{

    /**
     * @Route("/", name="home")
     */
    public function test(HubInterface $hub): Response
    {
        $update = new Update(
            'http://localhost:8084/books/1',
            json_encode(['status' => 'OutOfStock'])
        );

        $hub->publish($update);

        return new Response('published!');
    }

    /**
     * @Route("/client-test", name="client-test")
     */
    public function clientTest(): Response
    {
        return $this->render('home/client-test.html.twig');
    }

    /**
     * @Route("/", name="home")
     */
    public function index(): Response
    {
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    /**
     * @Route("/establishment/profile", name="establishment_profile")
     */
    public function profile(): Response
    {
        return $this->render('establishment/profile/index.html.twig', [
            'subscriptionActive'=>$this->getUser()->isSubscriptionActive(),
        ]);
    }

    /**
     * @Route("/establishment/dashboard", name="establishment_dashboard")
     */
    public function dashboard(): Response
    {
        return $this->render('establishment/dashboard/index.html.twig', [
            'subscriptionActive'=>$this->getUser()->isSubscriptionActive(),
        ]);
    }

    /**
     * @Route("/contact", name="contact")
     */
    public function contact(): Response
    {
        return $this->render('home/contact.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }
}
