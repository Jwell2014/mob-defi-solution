<?php

namespace App\Service;

use App\Exception\UnknownStationException;
use App\Exception\NoRouteFoundException;

class RouteCalculator
{
    public function __construct(
        private readonly RailNetwork $network
    ) {
    }

    /**
     * Calcule le plus court chemin entre 2 stations (par leurs codes : MX, ZW, ...).
     *
     * @throws UnknownStationException
     * @throws NoRouteFoundException
     */
    public function calculate(string $fromStationId, string $toStationId): array
    {
        // 1. Cas trivial : départ = arrivée
        if ($fromStationId === $toStationId) {
            if (!$this->network->hasStation($fromStationId)) {
                throw new UnknownStationException($fromStationId);
            }
            return [
                'distanceKm' => 0.0,
                'path'       => [$fromStationId],
            ];
        }

        // 2. Validation de base : les stations doivent exister
        if (!$this->network->hasStation($fromStationId)) {
            throw new UnknownStationException($fromStationId);
        }

        if (!$this->network->hasStation($toStationId)) {
            throw new UnknownStationException($toStationId);
        }

        // 3. Dijkstra "simple" sur l'ensemble du réseau

        $stationCodes = $this->network->getStationByCode(); // MX, ZW, etc.

        // Distance minimale connue depuis la source
        $dist = [];
        // Station précédente dans le plus court chemin
        $prev = [];
        // Ensemble des stations non encore "fixées"
        $unvisited = [];

        foreach ($stationCodes as $code) {
            $dist[$code] = INF;
            $prev[$code] = null;
            $unvisited[$code] = true;
        }

        $dist[$fromStationId] = 0.0;

        while (!empty($unvisited)) {
            // 3.a : trouver la station non visitée avec la plus petite distance
            $current = null;
            $currentDist = INF;

            foreach ($unvisited as $code => $_) {
                if ($dist[$code] < $currentDist) {
                    $currentDist = $dist[$code];
                    $current = $code;
                }
            }

            // Si on n'a plus de station atteignable, on arrête
            if ($current === null || $currentDist === INF) {
                break;
            }

            // Si on est arrivé à la destination, on peut stopper l'algorithme
            if ($current === $toStationId) {
                break;
            }

            // On marque la station comme visitée
            unset($unvisited[$current]);

            // 3.b : on met à jour les distances pour les voisins
            $neighbors = $this->network->getNeighbors($current);

            foreach ($neighbors as $neighborCode => $distanceKm) {
                // Si le voisin est déjà "fixé", on le saute
                if (!isset($unvisited[$neighborCode])) {
                    continue;
                }

                $alt = $dist[$current] + $distanceKm;

                if ($alt < $dist[$neighborCode]) {
                    $dist[$neighborCode] = $alt;
                    $prev[$neighborCode] = $current;
                }
            }
        }

        // 4. Vérifier si on a trouvé un chemin vers la destination
        if (!isset($dist[$toStationId]) || $dist[$toStationId] === INF) {
           throw new NoRouteFoundException($fromStationId, $toStationId);
        }

        // 5. Reconstruire le chemin en remontant depuis la destination
        $path = [];
        $current = $toStationId;

        while ($current !== null) {
            array_unshift($path, $current);
            $current = $prev[$current] ?? null;
        }

        // 6. Retourner le résultat
        return [
            'distanceKm' => round($dist[$toStationId], 2),
            'path'       => $path,
        ];
    }
}