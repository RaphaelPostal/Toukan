<?php

namespace App\Controller;

use Stripe\Customer;
use Stripe\Product;
use Stripe\Stripe;
use Stripe\Subscription;
use Stripe\SubscriptionItem;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
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
     * @Route("/profile", name="establishment_profile")
     */
    public function profile(): Response
    {
        return $this->render('establishment/profile/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    /**
     * @Route("/dashboard", name="establishment_dashboard")
     */
    public function dashboard(): Response
    {
        dd($this->getUser()->isSubscriptionActive());
        return $this->render('establishment/dashboard/index.html.twig', [
            'controller_name' => 'HomeController',
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
