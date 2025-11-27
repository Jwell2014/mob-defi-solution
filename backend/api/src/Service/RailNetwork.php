<?php

namespace App\Service;

class RailNetwork
{
    private array $stations;
    private array $distances;

    public function __construct(string $dataDir)
    {
        $stationsPath  = $dataDir . '/stations.json';
        $distancesPath = $dataDir . '/distances.json';

        $stationsJson  = file_get_contents($stationsPath);
        $distancesJson = file_get_contents($distancesPath);

        $this->stations  = json_decode($stationsJson, true) ?? [];
        $this->distances = json_decode($distancesJson, true) ?? [];
    }

    public function getStations(): array
    {
        return $this->stations;
    }

    public function getDistances(): array
    {
        return $this->distances;
    }

    public function getStationIds(): array
    {
        return array_column($this->stations, 'id');
    }
}