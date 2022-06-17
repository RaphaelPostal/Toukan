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
        $qrCodeTemplate = QrCodeTemplate::tryFrom($this->getUser()->getEstablishment()->getQrCodeTemplate());

        if (!$qrCodeTemplate) {
            return new JsonResponse(['error' => 'Template QrCode introuvable'], Response::HTTP_NOT_FOUND);
        } elseif (!$this->getUser()->getEstablishment()->getId() || $this->getUser()->getEstablishment()->getCustomColor()) {
            return new JsonResponse(['error' => 'Infos établissement manquantes'], Response::HTTP_NOT_FOUND);
        }

        $infos = [
            'qrCodeOptions' => [
                'dotsOption' => $qrCodeTemplate->getDotsOption(),
                'cornerSquareOption' => $qrCodeTemplate->getCornerSquareOption(),
                'cornerDotsOption' => $qrCodeTemplate->getCornerDotOption(),
            ],
            'establishmentId' => $this->getUser()->getEstablishment()->getId(),
            'establishmentImg' => "/assets/img/meltdown_logo.png",
            'establishmentColor' => $this->getUser()->getEstablishment()->getCustomColor(),
//            'establishmentImg' => "/assets/img/toukan_orange.png",
//            'establishmentImg' => "/assets/img/netto_logo.png",
        ];
        return new JsonResponse($infos, Response::HTTP_OK);
    }
}
