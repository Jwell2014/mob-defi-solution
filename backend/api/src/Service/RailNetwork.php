<?php

namespace App\Service;

class RailNetwork
{
    /**
     * Stations indexées par code (shortName) :
     * [
     *   'MX' => ['id' => 46, 'shortName' => 'MX', 'longName' => 'Montreux'],
     *   'ZW' => [...],
     *   ...
     * ]
     *
     * @var array<string, array>
     */
    private array $stationsByCode = [];

    /** @var array<int, array> */
    private array $lines = [];

    /**
     * Graphe d'adjacence :
     * [
     *   'MX' => ['CGE' => 0.65, ...],
     *   'CGE' => ['MX' => 0.65, 'VUAR' => 0.35],
     *   ...
     * ]
     *
     * @var array<string, array<string,float>>
     */
    private array $graph = [];

    public function __construct(string $dataDir)
    {
        $stationsPath  = $dataDir . '/stations.json';
        $distancesPath = $dataDir . '/distances.json';

        $stationsJson  = file_get_contents($stationsPath);
        $distancesJson = file_get_contents($distancesPath);

        $stations    = json_decode($stationsJson, true) ?? [];
        $this->lines = json_decode($distancesJson, true) ?? [];

        // Indexation par shortName (MX, ZW, …)
        foreach ($stations as $station) {
            if (!isset($station['shortName'])) {
                continue;
            }
            $code = $station['shortName'];
            $this->stationsByCode[$code] = $station;
        }

        $this->buildGraph();
    }

    private function buildGraph(): void
    {
        $this->graph = [];

        foreach ($this->lines as $line) {
            $edges = $line['distances'] ?? [];

            foreach ($edges as $edge) {
                $from     = $edge['parent'] ?? null;
                $to       = $edge['child'] ?? null;
                $distance = $edge['distance'] ?? null;

                if (!$from || !$to || $distance === null) {
                    continue;
                }

                if (!isset($this->graph[$from])) {
                    $this->graph[$from] = [];
                }
                if (!isset($this->graph[$to])) {
                    $this->graph[$to] = [];
                }

                // Réseau non orienté : A <-> B
                $this->graph[$from][$to] = (float) $distance;
                $this->graph[$to][$from] = (float) $distance;
            }
        }
    }

    /**
     * Retourne la liste des codes de stations (MX, ZW, …).
     *
     * @return string[]
     */
    public function getStationByCode(): array
    {
        return array_keys($this->stationsByCode);
    }

    /**
     * Retourne les voisins d'une station :
     * [ stationVoisine => distanceKm, ... ]
     *
     * @return array<string,float>
     */
    public function getNeighbors(string $stationCode): array
    {
        return $this->graph[$stationCode] ?? [];
    }

    public function hasStation(string $stationCode): bool
    {
        return array_key_exists($stationCode, $this->stationsByCode);
    }
}