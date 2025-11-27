<?php

namespace App\Service;

class RouteCalculator
{
    public function __construct(
        private readonly RailNetwork $network
    ) {
    }

    /**
     * Calcule (pour l'instant de façon simplifiée) un trajet entre 2 stations.
     *
     * Retourne un tableau avec :
     *  - distanceKm (float)
     *  - path (array<string>)
     */
    public function calculate(string $fromStationId, string $toStationId): array
    {
        // TODO: plus tard -> implémenter un vrai algorithme de plus court chemin

        // Pour l'instant, on renvoie juste un "stub" qui respecte la forme
        // en disant "le chemin est direct" si les 2 stations existent.

        if (!$this->network->hasStation($fromStationId) || !$this->network->hasStation($toStationId)) {
            // on pourrait ici lever une exception custom qu'on mappera en 422
            // pour l'instant, on renvoie un résultat minimal
            return [
                'distanceKm' => 0.0,
                'path'       => [],
            ];
        }

        // Stub : distance fictive + chemin direct
        // (ce sera remplacé par le vrai calcul)
        return [
            'distanceKm' => 123.4,
            'path'       => [$fromStationId, $toStationId],
        ];
    }
}