<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\RailNetwork;
use App\Service\RouteCalculator;
use App\Exception\NoRouteFoundException;
use App\Exception\UnknownStationException;
use App\Service\RouteStorage;

class RouteController extends AbstractController
{
    #[Route('/api/v1/routes', name: 'calculate_route', methods: ['POST'])]
    public function calculateRoute(
        Request $request, 
        RailNetwork $network,
        RouteCalculator $calculator, 
        RouteStorage $routeStorage): JsonResponse    
        {
        // 1. Parsing JSON
        $data = json_decode($request->getContent(), true);

        if (!is_array($data)) {
            return $this->errorResponse(
                'INVALID_REQUEST',
                'Le corps de la requête doit être un objet JSON valide.',
                ['Le corps du message n\'est pas un objet JSON valide.'],
                400
            );
        }

        // 2. Validation des champs requis
        $requiredFields = ['fromStationId', 'toStationId', 'analyticCode'];
        $details = [];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || !is_string($data[$field]) || $data[$field] === '') {
                $details[] = sprintf('Le champ "%s" est obligatoire et doit être une chaîne de caractères non vide.', $field);
            }
        }

        if (!empty($details)) {
            return $this->errorResponse(
                'INVALID_REQUEST',
                'Certains champs obligatoires sont manquants ou invalides.',
                $details,
                400
            );
        }

        $fromStationId = $data['fromStationId'];
        $toStationId   = $data['toStationId'];
        $analyticCode  = $data['analyticCode'];
        
        try {
            $result = $calculator->calculate($fromStationId, $toStationId);
        } catch (UnknownStationException $e) {
            return $this->errorResponse(
                'UNKNOWN_STATION',
                $e->getMessage(),
                $e->getDetails(),
                422
            );
        } catch (NoRouteFoundException $e) {
            return $this->errorResponse(
                'NO_ROUTE',
                $e->getMessage(),
                $e->getDetails(),
                422
            );
        }

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
        'debug_neighborsFrom' => $network->getNeighbors($fromStationId),
        ];

        // Persistance simple du trajet pour les statistiques
        $routeStorage->append([
            'id'            => $route['id'],
            'fromStationId' => $route['fromStationId'],
            'toStationId'   => $route['toStationId'],
            'analyticCode'  => $route['analyticCode'],
            'distanceKm'    => $route['distanceKm'],
            'createdAt'     => $route['createdAt'],
        ]);

        return new JsonResponse($route, 201);
    }

     private function errorResponse(string $code, string $message, array $details, int $status): JsonResponse
    {
        return new JsonResponse(
            [
                'code'    => $code,
                'message' => $message,
                'details' => $details,
            ],
            $status
        );
    }
}