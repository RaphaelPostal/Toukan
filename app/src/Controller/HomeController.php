<?php

namespace App\Controller;

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
