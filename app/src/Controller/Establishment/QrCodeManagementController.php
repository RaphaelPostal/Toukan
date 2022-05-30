<?php

namespace App\Controller\Establishment;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class QrCodeManagementController extends AbstractController
{
    #[Route('/qr-code/management', name: 'app_qr_code_management')]
    public function index(): Response
    {
        return $this->render('establishment/qr_code_management/index.html.twig', [
            'controller_name' => 'QrCodeManagementController',
        ]);
    }

    #[Route('/qr-code/print', name: 'app_qr_code_management')]
    public function printQrCode(): Response
    {
        return $this->render('establishment/qr_code_management/print.html.twig', ['establishmentId' => 1]);
    }
}
