<?php

namespace App\Tests\Service;

use App\Exception\NoRouteFoundException;
use App\Exception\UnknownStationException;
use App\Service\RailNetwork;
use App\Service\RouteCalculator;
use PHPUnit\Framework\TestCase;

class RouteCalculatorTest extends TestCase
{
    private RouteCalculator $calculator;

    protected function setUp(): void
    {
        // On utilise les VRAIS fichiers JSON du projet
        $dataDir  = __DIR__ . '/../../../data';
        $network  = new RailNetwork($dataDir);
        $this->calculator = new RouteCalculator($network);
    }

    /**
     * Vérifie que le calculateur retourne bien une distance > 0
     * et un chemin non vide entre deux stations valides (MX -> ZW).
     */
    public function testCalculateReturnsPathAndDistanceForValidStations(): void
    {
        $result = $this->calculator->calculate('MX', 'ZW');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('distanceKm', $result);
        $this->assertArrayHasKey('path', $result);

        $this->assertIsFloat($result['distanceKm']);
        $this->assertGreaterThan(0, $result['distanceKm']);

        $this->assertIsArray($result['path']);
        $this->assertNotEmpty($result['path']);

        // Le chemin doit commencer par MX et finir par ZW
        $this->assertSame('MX', $result['path'][0]);
        $this->assertSame('ZW', end($result['path']));
    }


    /**
     * Vérifie que le calculateur lève une UnknownStationException
     * lorsqu'on lui fournit une station de départ inconnue (XXX).
     */
    public function testCalculateThrowsForUnknownFromStation(): void
    {
        $this->expectException(UnknownStationException::class);

        $this->calculator->calculate('XXX', 'ZW');
    }

    /**
     * Vérifie que le calculateur lève une NoRouteFoundException
     * lorsque les deux stations existent mais qu'aucun chemin n'est possible
     * dans le graphe (ex. CAUX -> MX).
     */
    public function testCalculateThrowsWhenNoRouteExists(): void
    {
        $this->expectException(NoRouteFoundException::class);

        $this->calculator->calculate('CAUX', 'MX');
    }
}
