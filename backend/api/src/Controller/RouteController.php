<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\RailNetwork;
use App\Service\RouteCalculator;

class RouteController extends AbstractController
{
    #[Route('/api/v1/routes', name: 'calculate_route', methods: ['POST'])]
    public function calculateRoute(Request $request, RailNetwork $network, RouteCalculator $calculator): JsonResponse    {
        $data = json_decode($request->getContent(), true) ?? [];

        // TODO plus tard : validation propre (400 / 422) + appel à un service de calcul de route

        $fromStationId = $data['fromStationId'] ?? 'MX';
        $toStationId   = $data['toStationId'] ?? 'ZW';
        $analyticCode  = $data['analyticCode'] ?? 'ANA-123';
        $result = $calculator->calculate($fromStationId, $toStationId);

        $neighborsFrom = $network->getNeighbors($fromStationId);

        // Squelette d'une "Route" conforme au schéma OpenAPI
        $route = [
        'id'            => 'route-' . uniqid(),
        'fromStationId' => $fromStationId,
        'toStationId'   => $toStationId,
        'analyticCode'  => $analyticCode,
        'distanceKm'    => $result['distanceKm'],
        'path'          => $result['path'],
        'createdAt'     => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
        'debug_stationCount'  => count($network->getStationByCode()),
        'debug_neighborsFrom' => $neighborsFrom,
        ];

        return new JsonResponse($route, 201);
    }
}