<?php

namespace App\Controller\Api;

use App\Config\QrCodeTemplate;
use App\Repository\EstablishmentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class QrCodeApi extends AbstractController
{
    #[Route('/restaurant/qr-code-options}', name: 'api_qr_code_options', methods: ['GET'])]
    public function getQrCode(EstablishmentRepository $establishmentRepository): JsonResponse
    {
//        $qrCodes = $establishmentRepository->find($establishmentId);
        $qrCodeTemplate = QrCodeTemplate::BASIC;
        $infos = [
            'qrCodeOptions' => [
                'dotsOption' => $qrCodeTemplate->getDotsOption(),
                'cornerSquareOption' => $qrCodeTemplate->getCornerSquareOption(),
                'cornerDotsOption' => $qrCodeTemplate->getCornerDotOption(),
            ],
            'establishmentId' => $this->getUser()->getEstablishment()->getId(),
//            'establishmentImg' => "/assets/img/toukan_orange.png",
//            'establishmentColor' => "#F49B22",
            'establishmentImg' => "/assets/img/meltdown_logo.png",
            'establishmentColor' => "#97e300",
//            'establishmentImg' => "/assets/img/netto_logo.png",
//            'establishmentColor' => "#fc3503",
        ];
        return new JsonResponse($infos, Response::HTTP_OK);
    }
}
