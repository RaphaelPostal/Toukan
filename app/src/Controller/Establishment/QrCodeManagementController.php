<?php

namespace App\Controller\Establishment;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class QrCodeManagementController extends AbstractController
{
    #[Route('/qr-code/print', name: 'app_qr_code_print')]
    public function printQrCode(): Response
    {
        return $this->render('establishment/qr_code_management/print.html.twig', ['establishmentId' => $this->getUser()->getEstablishment()->getId()]);
    }
}
