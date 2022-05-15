<?php

namespace App\Controller\Establishment;

use App\Entity\Establishment;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EstablishmentManageController extends AbstractController
{
    #[Route('/establishment/manage', name: 'app_establishment_manage')]
    public function index(): Response
    {
        //$establishment = $this->getUser()->getEstablishment();

        $establishment =$this->getDoctrine()->getRepository(Establishment::class)->find(1);
        $user = $this->getDoctrine()->getRepository(User::class)->find(1);

        return $this->render('establishment/information/index.html.twig', [
            'user' => $user,
            'establishment' => $establishment,
        ]);
    }
}
