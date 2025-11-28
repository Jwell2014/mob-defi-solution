<?php

namespace App\Exception;

// Quand une station n’existe pas
class UnknownStationException extends \RuntimeException
{
    public function __construct(
        private readonly string $stationCode
    ) {
        parent::__construct(sprintf('La station code "%s" n\'existe pas sur le réseau.', $stationCode));
    }

    /**
     * Détails pour le schéma Error.details
     *
     * @return string[]
     */
    public function getDetails(): array
    {
        return [sprintf('La station code "%s" n\'existe pas sur le réseau.', $this->stationCode)];
    }
}