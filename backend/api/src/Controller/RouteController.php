<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\RailNetwork;

class RouteController extends AbstractController
{
    #[Route('/api/v1/routes', name: 'calculate_route', methods: ['POST'])]
    public function calculateRoute(Request $request, RailNetwork $network): JsonResponse    {
        $data = json_decode($request->getContent(), true) ?? [];

        // TODO plus tard : validation propre (400 / 422) + appel à un service de calcul de route

        $fromStationId = $data['fromStationId'] ?? 'MX';
        $toStationId   = $data['toStationId'] ?? 'ZW';
        $analyticCode  = $data['analyticCode'] ?? 'ANA-123';

        // Squelette d'une "Route" conforme au schéma OpenAPI
        $route = [
            'id'            => 'route-' . uniqid(),
            'fromStationId' => $fromStationId,
            'toStationId'   => $toStationId,
            'analyticCode'  => $analyticCode,
            'distanceKm'    => 123.4,                  // valeur bidon pour l'instant
            'path'          => [$fromStationId, $toStationId], // on fera le vrai chemin plus tard
            'createdAt'     => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
        ];

        return new JsonResponse($route, 201);
    }
}