<?php

namespace App\Exception;

// Quand il n’y a pas de chemin possible entre A et B
class NoRouteFoundException extends \RuntimeException
{
    public function __construct(
        private readonly string $fromStation,
        private readonly string $toStation
    ) {
        parent::__construct(sprintf('No route found between "%s" and "%s".', $fromStation, $toStation));
    }

    /**
     * Détails pour le schéma Error.details
     *
     * @return string[]
     */
    public function getDetails(): array
    {
        return [sprintf('Le réseau n\'a aucun chemin de "%s" à "%s".', $this->fromStation, $this->toStation)];
    }
}