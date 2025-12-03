<?php

namespace App\Service;

class RouteStorage
{
    public function __construct(
        private readonly string $storageFile
    ) {
    }

    /**
     * Charge tous les trajets persistés.
     *
     * @return array<int, array>
     */
    public function loadAll(): array
    {
        if (!file_exists($this->storageFile)) {
            return [];
        }

        $json = file_get_contents($this->storageFile);
        if ($json === false || $json === '') {
            return [];
        }

        $data = json_decode($json, true);
        return is_array($data) ? $data : [];
    }

    /**
     * Ajoute un trajet et réécrit le fichier JSON.
     *
     * @param array $route tableau représentant une Route (id, fromStationId, ...)
     */
    public function append(array $route): void
    {
        $all = $this->loadAll();
        $all[] = $route;

        // On s'assure que le dossier existe
        $dir = dirname($this->storageFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        file_put_contents(
            $this->storageFile,
            json_encode($all, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            LOCK_EX
        );
    }
}